<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Capacity;
use Lemuria\Engine\Fantasya\Effect\Airship;
use Lemuria\Engine\Fantasya\Effect\SneakPastEffect;
use Lemuria\Engine\Fantasya\Effect\TravelEffect;
use Lemuria\Engine\Fantasya\Message\Region\TravelUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelCanalMessage;
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
	use MoveTrait;

	private ?Vessel $vessel = null;

	private Capacity $capacity;

	private Workload $workload;

	private int $roadsLeft = 0;

	private bool $hasTravelled = false;

	private Airship|false|null $airship = false;

	private bool $airshipped = false;

	protected function canMoveTo(Direction $direction): ?Region {
		$this->airshipped = false;
		$region = $this->unit->Region();
		/** @var Region $neighbour */
		$neighbour = Lemuria::World()->getNeighbours($region)[$direction] ?? null;
		if (!$neighbour) {
			$this->message(TravelIntoChaosMessage::class)->p($direction->value);
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
					if ($this->useAirshipEffect()) {
						$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
						return $neighbour;
					}
					$this->message(TravelAnchorMessage::class, $this->vessel)->p($direction->value)->p($anchor->value, TravelAnchorMessage::ANCHOR);
					return null;
				}
			}
			if ($landscape instanceof Ocean) {
				$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
				return $neighbour;
			}
			if ($region->Landscape() instanceof Ocean) {
				if (!$this->canSailTo($neighbour) && !$this->useAirshipEffect()) {
					$this->message(TravelLandMessage::class, $this->vessel)->p($direction->value)->s($landscape)->e($neighbour);
					return null;
				}
				$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
				return $neighbour;
			}
			if ($this->canUseCanal($neighbour)) {
				$this->message(TravelCanalMessage::class)->e($region);
				return $neighbour;
			}
			if ($this->useAirshipEffect()) {
				$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
				return $neighbour;
			}
			$this->message(TravelOverLandMessage::class, $this->vessel)->p($direction->value);
			return null;
		}

		if ($this->capacity->Movement() === Capacity::FLY) {
			$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
			return $neighbour;
		}

		if ($landscape instanceof Ocean) {
			$this->message(TravelIntoOceanMessage::class)->p($direction->value);
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

		$this->clearUnitStatus($this->unit);
		$this->clearConstructionOwner($this->unit);

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
		$effect = new SneakPastEffect(State::getInstance());
		if (Lemuria::Score()->find($effect->setUnit($this->unit))) {
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
		$remaining     = new Gathering();
		$isSimultation = $this->context->getTurnOptions()->IsSimulation();
		foreach ($guards as $party /* @var Party $party */) {
			$remaining->add($party);
			if ($isSimultation) {
				continue;
			}
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
			Direction::North     => Direction::South,
			Direction::Northeast => Direction::Southwest,
			Direction::East      => Direction::West,
			Direction::Southeast => Direction::Northwest,
			Direction::South     => Direction::North,
			Direction::Southwest => Direction::Northeast,
			Direction::West      => Direction::East,
			Direction::Northwest => Direction::Southeast,
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

	private function useAirshipEffect(): bool {
		$airship = $this->airshipEffect();
		if ($airship) {
			if ($this->hasTravelled && !$airship->continueEffect()) {
				return false;
			}
			$this->airshipped = true;
			return true;
		}
		return false;
	}

	private function airshipEffect(): ?Airship {
		if ($this->airship === false) {
			$effect        = new Airship(State::getInstance());
			$effect        = Lemuria::Score()->find($effect->setVessel($this->vessel));
			$this->airship = $effect instanceof Airship ? $effect : null;
		}
		return $this->airship;
	}
}
