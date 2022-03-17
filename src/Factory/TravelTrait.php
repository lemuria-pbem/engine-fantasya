<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Capacity;
use Lemuria\Engine\Fantasya\Effect\TravelEffect;
use Lemuria\Engine\Fantasya\Message\Construction\LeaveNewOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Construction\LeaveNoOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Region\TravelUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveConstructionDebugMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelCanalMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelGuardCancelMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelIntoChaosMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelIntoOceanMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNeighbourMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelAnchorMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelLandMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelOverLandMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Canal;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;
use Lemuria\Model\World\Direction;

trait TravelTrait
{
	use BuilderTrait;
	use ContextTrait;

	private ?Vessel $vessel = null;

	private Capacity $capacity;

	private Workload $workload;

	private int $roadsLeft = 0;

	private bool $hasTravelled = false;

	protected function canMoveTo(Direction $direction): ?Region {
		$region = $this->unit->Region();
		/** @var Region $neighbour */
		$neighbour = Lemuria::World()->getNeighbours($region)[$direction] ?? null;
		if (!$neighbour) {
			$this->message(TravelIntoChaosMessage::class)->p($direction);
			return null;
		}
		$landscape = $neighbour->Landscape();

		if ($this->capacity->Movement() === Capacity::SHIP) {
			$anchor = $this->vessel->Anchor();
			if ($anchor !== Direction::IN_DOCK) {
				if ($direction !== $anchor) {
					if ($this->canUseCanal($neighbour)) {
						$this->message(TravelCanalMessage::class)->e($region);
						return $neighbour;
					}
					$this->message(TravelAnchorMessage::class, $this->vessel)->p($direction)->p($anchor, TravelAnchorMessage::ANCHOR);
					return null;
				}
			}
			if ($landscape instanceof Ocean) {
				$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
				return $neighbour;
			}
			if ($region->Landscape() instanceof Ocean) {
				if (!$this->canSailTo($neighbour)) {
					$this->message(TravelLandMessage::class, $this->vessel)->p($direction)->s($landscape)->e($neighbour);
					return null;
				}
				$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
				return $neighbour;
			}
			if ($this->canUseCanal($neighbour)) {
				$this->message(TravelCanalMessage::class)->e($region);
				return $neighbour;
			}
			$this->message(TravelOverLandMessage::class, $this->vessel)->p($direction);
			return null;
		}

		if ($this->capacity->Movement() === Capacity::FLY) {
			$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
			return $neighbour;
		}

		if ($landscape instanceof Ocean) {
			$this->message(TravelIntoOceanMessage::class)->p($direction);
			return null;
		}
		$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
		return $neighbour;
	}

	protected function setRoadsLeft(string $movement): void {
		$this->roadsLeft = match ($movement) {
			Capacity::RIDE => 3,
			Capacity::WALK => 2,
			default        => 0
		};
	}

	protected function moveTo(Region $destination): void {
		$region = $this->unit->Region();

		if ($this->unit->IsGuarding()) {
			$this->unit->setIsGuarding(false);
			$this->message(TravelGuardCancelMessage::class);
		}

		$construction = $this->unit->Construction();
		if ($construction) {
			$isOwner = $construction->Inhabitants()->Owner() === $this->unit;
			$construction->Inhabitants()->remove($this->unit);
			$this->message(LeaveConstructionDebugMessage::class)->e($construction);
			if ($isOwner) {
				$owner = $construction->Inhabitants()->Owner();
				if ($owner) {
					$this->message(LeaveNewOwnerMessage::class, $construction)->e($owner);
				} else {
					$this->message(LeaveNoOwnerMessage::class, $construction);
				}
			}
		}

		if ($this->vessel) {
			$this->moveVessel($destination);
		} else {
			$region->Residents()->remove($this->unit);
			$destination->Residents()->add($this->unit);
			$this->createTravelEffect();
			$this->unit->Party()->Chronicle()->add($destination);
			if (!$this->unit->IsHiding()) {
				$this->message(TravelUnitMessage::class, $region)->p((string)$this->unit);
			}
		}

		$this->hasTravelled = true;
	}

	/**
	 * @noinspection PhpConditionAlreadyCheckedInspection
	 */
	protected function unitIsStoppedByGuards(Region $region): Gathering {
		$guards = new Gathering();
		if ($this->context->getTurnOptions()->IsSimulation()) {
			return $guards;
		}

		$isOnVessel   = (bool)$this->unit->Vessel();
		$intelligence = $this->context->getIntelligence($region);
		$camouflage   = $this->calculus()->knowledge(Camouflage::class)->Level();
		foreach ($intelligence->getGuards() as $guard /* @var Unit $guard */) {
			$guardParty = $guard->Party();
			if ($guardParty !== $this->unit->Party()) {
				$guardOnVessel = (bool)$guard->Vessel();
				if ($guardOnVessel === $isOnVessel) {
					if (!$guardParty->Diplomacy()->has(Relation::GUARD, $this->unit)) {
						if ($region instanceof Ocean) {
							$guards->add($guardParty);
						} else {
							$perception = $this->context->getCalculus($guard)->knowledge(Perception::class)->Level();
							if ($perception >= $camouflage) {
								$guards->add($guardParty);
							}
						}
					}
				}
			}
		}
		return $guards;
	}

	/**
	 * If unit is allowed to pass region, check if next region of the journey is guarded by same party.
	 * If it is guarded, check if unit is also allowed to pass or has GUARD relation.
	 */
	protected function unitIsAllowedToPass(Region $region, Gathering $guards): Gathering {
		$remaining = new Gathering();
		foreach ($guards as $party /* @var Party $party */) {
			$remaining->add($party);
			$diplomacy = $party->Diplomacy();
			if ($diplomacy->has(Relation::PASS, $this->unit)) {
				if ($this->directions->hasMore()) {
					$direction = $this->directions->peek();
					$neighbour = Lemuria::World()->getNeighbours($region)[$direction] ?? null;
					if ($neighbour) {
						$nextGuards = $this->context->getIntelligence($neighbour)->getGuards();
						if ($nextGuards->isEmpty()) {
							$remaining->remove($party);
						} else {
							$allowToPass = true;
							foreach ($nextGuards as $guard/* @var Unit $guard */) {
								if ($guard->Party() === $party) {
									if (!$diplomacy->has(Relation::GUARD, $this->unit, $neighbour) && !$diplomacy->has(Relation::PASS, $this->unit, $neighbour)) {
										$allowToPass = false;
										break;
									}
								}
							}
							if ($allowToPass) {
								$remaining->remove($party);
							}
						}
					} else {
						$remaining->remove($party);
					}
				} else {
					$remaining->remove($party);
				}
			}
		}
		return $remaining;
	}

	protected function getOppositeDirection(Direction $direction): Direction {
		return match ($direction) {
			Direction::NORTH     => Direction::SOUTH,
			Direction::NORTHEAST => Direction::SOUTHWEST,
			Direction::EAST      => Direction::WEST,
			Direction::SOUTHEAST => Direction::NORTHWEST,
			Direction::SOUTH     => Direction::NORTH,
			Direction::SOUTHWEST => Direction::NORTHEAST,
			Direction::WEST      => Direction::EAST,
			Direction::NORTHWEST => Direction::SOUTHEAST,
			default              => throw new LemuriaException('Cannot determine opposite direction.')
		};
	}

	protected function overRoad(Region $from, Direction $direction, Region $to): bool {
		if ($from->hasRoad($direction)) {
			$direction = $this->getOppositeDirection($direction);
			if ($to->hasRoad($direction)) {
				$this->roadsLeft--;
				return true;
			}
		}
		$this->roadsLeft -= 2;
		return false;
	}

	protected function canUseCanal(Region $neighbour): bool {
		$party    = $this->unit->Party();
		$building = self::createBuilding(Canal::class);
		foreach ($this->unit->Region()->Estate() as $construction /* @var Construction $construction */) {
			if ($construction->Building() === $building) {
				$owner = $construction->Inhabitants()->Owner()?->Party();
				if ($owner && $owner !== $party && !$owner->Diplomacy()->has(Relation::PASS, $this->unit)) {
					continue;
				}
				if ($neighbour->Landscape() instanceof Ocean) {
					return true;
				}
			}
		}
		return false;
	}

	private function createTravelEffect(): void {
		$effect = new TravelEffect(State::getInstance());
		if (!Lemuria::Score()->find($effect->setUnit($this->unit))) {
			Lemuria::Score()->add($effect);
		}
	}
}
