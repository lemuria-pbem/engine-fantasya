<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel;

use function Lemuria\randElement;
use Lemuria\Engine\Fantasya\Effect\Airship;
use Lemuria\Engine\Fantasya\Effect\SneakPastEffect;
use Lemuria\Engine\Fantasya\Effect\TravelEffect;
use Lemuria\Engine\Fantasya\Effect\Unmaintained;
use Lemuria\Engine\Fantasya\Effect\UnpaidDemurrage;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Factory\ContextTrait;
use Lemuria\Engine\Fantasya\Factory\Workload;
use Lemuria\Engine\Fantasya\Message\Region\TravelUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelAlternativeChaosMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelAlternativeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelAlternativeNavigableMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelCanalFeeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelCanalMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelCanalPaidMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelIntoChaosMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelIntoOceanMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNeighbourMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelUnmaintainedPortMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelUnpaidCanalMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelUnpaidDemurrageMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelAnchorMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelLandMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelOverLandMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelPortDirectionMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Canal;
use Lemuria\Model\Fantasya\Chronicle;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Extension\Fee;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Party\Exploring;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Vessel;
use Lemuria\Model\World\Direction;

trait TravelTrait
{
	use BuilderTrait;
	use CollectTrait;
	use ContextTrait;
	use MoveTrait;

	private ?Vessel $vessel = null;

	private Trip $trip;

	private Workload $workload;

	private Chronicle $chronicle;

	private Exploring $exploring;

	private int $roadsLeft = 0;

	private bool $hasTravelled = false;

	private Airship|false|null $airship = false;

	private bool $airshipped = false;

	private array $neighbourDirections = [];

	protected function stopSimulation(Direction $direction): bool {
		$region = $this->unit->Region();
		/** @var Region $neighbour */
		$neighbour = Lemuria::World()->getNeighbours($region)[$direction] ?? null;
		return $neighbour && !$this->chronicle->has($neighbour->Id());
	}

	protected function canMoveTo(Direction $direction, bool $withFailure = true): ?Region {
		$this->airshipped = false;
		$region = $this->unit->Region();
		/** @var Region $neighbour */
		$neighbour = Lemuria::World()->getNeighbours($region)[$direction] ?? null;
		if (!$neighbour) {
			if ($withFailure) {
				$this->message(TravelIntoChaosMessage::class)->p($direction->value);
			}
			return null;
		}
		$landscape = $neighbour->Landscape();

		if ($this->trip->Movement() === Movement::Ship) {
			$anchor = $this->vessel->Anchor();
			if ($anchor !== Direction::IN_DOCK) {
				if ($direction !== $anchor) {
					if ($this->canLeavePort($direction, $neighbour)) {
						$this->message(TravelPortDirectionMessage::class, $this->vessel)->p($direction->value);
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
					$this->message(TravelAnchorMessage::class, $this->vessel)->p($direction->value)->p($anchor->value, TravelAnchorMessage::ANCHOR);
					return null;
				}
			}
			if ($landscape instanceof Navigable) {
				$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
				return $neighbour;
			}
			if ($region->Landscape() instanceof Navigable) {
				if (!$this->canSailTo($neighbour) && !$this->useAirshipEffect()) {
					if ($withFailure) {
						if ($this->exploring !== Exploring::Explore || $this->chronicle->has($neighbour->Id())) {
							$this->message(TravelLandMessage::class, $this->vessel)->p($direction->value)->s($landscape)->e($neighbour);
						}
					}
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

		if ($this->trip->Movement() === Movement::Fly) {
			$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
			return $neighbour;
		}

		if ($landscape instanceof Navigable) {
			if ($withFailure) {
				$this->message(TravelIntoOceanMessage::class)->p($direction->value);
			}
			return null;
		}
		$this->message(TravelNeighbourMessage::class)->p($direction->value)->s($landscape)->e($neighbour);
		return $neighbour;
	}

	protected function considerExploration(Direction &$direction, ?Region $region): ?Region {
		if ($this->exploring === Exploring::Not) {
			return $region;
		}

		$alternative = $region;
		if ($this->trip->Movement() === Movement::Ship) {
			if ($region && !$this->chronicle->has($region->Id())) {
				$landscape = $region->Landscape();
				if (!$landscape instanceof Navigable && $this->exploring === Exploring::Explore) {
					$alternatives = $this->chooseAlternativeNavigables($direction, $this->unit->Region());
					if (!empty($alternatives)) {
						$original    = $direction;
						$pick        = randElement($alternatives);
						$direction   = $pick[0];
						$alternative = $pick[1];
						$this->message(TravelAlternativeNavigableMessage::class)->p($original->value)->p($direction->value, TravelAlternativeMessage::ALTERNATIVE);
					}
				}
			} elseif (!$region) {
				$allowNavigable = $this->exploring === Exploring::Explore;
				$origin         = $this->unit->Region();
				/** @var Region|null $region */
				$region = Lemuria::World()->getNeighbours($origin)[$direction] ?? null;
				if ($region) {
					if ($allowNavigable) {
						$alternatives = $this->chooseAlternativeNavigables($direction, $origin);
					} else {
						$alternatives = $this->chooseAlternatives($direction, $origin);
					}
				} else {
					$alternatives = $this->chooseAlternativesToChaos($direction, $origin, $allowNavigable);
				}
				if (!empty($alternatives)) {
					$original    = $direction;
					$pick        = randElement($alternatives);
					$direction   = $pick[0];
					$alternative = $pick[1];
					if ($region) {
						$this->message(TravelAlternativeNavigableMessage::class)->p($original->value)->p($direction->value, TravelAlternativeMessage::ALTERNATIVE);
					} else {
						$this->message(TravelAlternativeChaosMessage::class)->p($original->value)->p($direction->value, TravelAlternativeMessage::ALTERNATIVE);
					}
				}
			}
		} else {
			if (!$region) {
				$origin = $this->unit->Region();
				/** @var Region|null $region */
				$region = Lemuria::World()->getNeighbours($origin)[$direction] ?? null;
				if ($region) {
					$alternatives = $this->chooseAlternatives($direction, $origin);
				} else {
					$alternatives = $this->chooseAlternativesToChaos($direction, $origin);
				}
				if (!empty($alternatives)) {
					$original    = $direction;
					$pick        = randElement($alternatives);
					$direction   = $pick[0];
					$alternative = $pick[1];
					if ($region) {
						$this->message(TravelAlternativeMessage::class)->p($original->value)->p($direction->value, TravelAlternativeMessage::ALTERNATIVE);
					} else {
						$this->message(TravelAlternativeChaosMessage::class)->p($original->value)->p($direction->value, TravelAlternativeMessage::ALTERNATIVE);
					}
				}
			}
		}
		return $alternative;
	}

	protected function setRoadsLeft(Movement $movement): void {
		$this->roadsLeft = match ($movement) {
			Movement::Ride => 3,
			Movement::Walk => 2,
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
		$camouflage   = $this->calculus()->camouflage()->Level();
		foreach ($intelligence->getGuards() as $guard) {
			$guardParty = $guard->Party();
			if ($guardParty !== $this->unit->Party()) {
				$guardOnVessel = (bool)$guard->Vessel();
				if ($guardOnVessel === $isOnVessel) {
					if (!$guardParty->Diplomacy()->has(Relation::GUARD, $this->unit)) {
						if ($region instanceof Navigable) {
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

	protected function unitIsBlockedByGuards(Region $region, Direction $direction): People {
		$guards = new People();
		$effect = new SneakPastEffect(State::getInstance());
		if (Lemuria::Score()->find($effect->setUnit($this->unit))) {
			return $guards;
		}

		$isSimulation = $this->context->getTurnOptions()->IsSimulation();
		$blockade     = $isSimulation ? null : $this->context->getBlockade($region, $direction);
		$isOnVessel   = (bool)$this->unit->Vessel();
		$intelligence = $this->context->getIntelligence($region);
		$camouflage   = $this->calculus()->camouflage()->Level();
		foreach ($intelligence->getGuards() as $guard) {
			$guardParty = $guard->Party();
			if ($guardParty !== $this->unit->Party()) {
				$guardOnVessel = (bool)$guard->Vessel();
				if ($guardOnVessel === $isOnVessel) {
					if ($guard->GuardDirection() === $direction) {
						if ($isSimulation) {
							$guards->add($guard);
							continue;
						}
						if ($blockade->add($guard)->block($this->unit)) {
							if (!$guardParty->Diplomacy()->has(Relation::GUARD, $this->unit)) {
								if ($region instanceof Navigable) {
									$guards->add($guard);
								} else {
									$perception = $this->context->getCalculus($guard)->knowledge(Perception::class)->Level();
									if ($perception >= $camouflage) {
										$guards->add($guard);
									}
								}
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
		$isSimulation = $this->context->getTurnOptions()->IsSimulation();
		foreach ($guards as $party) {
			$remaining->add($party);
			if ($isSimulation) {
				continue;
			}
			$diplomacy = $party->Diplomacy();
			if ($diplomacy->has(Relation::PASS, $this->unit)) {
				$allowToPass = true;
				if ($this->directions->hasMore()) {
					$direction = $this->directions->peek();
					/** @var Region|null $neighbour */
					$neighbour = Lemuria::World()->getNeighbours($region)[$direction] ?? null;
					if ($neighbour) {
						$nextGuards = $this->context->getIntelligence($neighbour)->getGuards();
						if (!$nextGuards->isEmpty()) {
							foreach ($nextGuards as $guard) {
								if ($guard->Party() === $party) {
									if (!$diplomacy->has(Relation::GUARD, $this->unit, $neighbour) && !$diplomacy->has(Relation::PASS, $this->unit, $neighbour)) {
										$allowToPass = false;
										break;
									}
								}
							}
						}
					}
				}
				if ($allowToPass) {
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

	protected function canLeavePort(Direction $direction, Region $neighbour): bool {
		if ($this->vessel->Port() && $neighbour->Landscape() instanceof Navigable) {
			$effect = new Unmaintained(State::getInstance());
			if (Lemuria::Score()->find($effect->setConstruction($this->vessel->Port()))) {
				$this->message(TravelUnmaintainedPortMessage::class)->p($direction->value);
				return false;
			}
			$effect = new UnpaidDemurrage(State::getInstance());
			if (Lemuria::Score()->find($effect->setVessel($this->vessel))) {
				$this->message(TravelUnpaidDemurrageMessage::class)->p($direction->value);
				return false;
			}
			$this->initNeighbourDirections();
			$directions = $this->neighbourDirections[$this->vessel->Anchor()->value];
			return $direction === $directions[0] || $direction === $directions[1];
		}
		return false;
	}

	protected function canUseCanal(Region $neighbour): bool {
		$party    = $this->unit->Party();
		$building = self::createBuilding(Canal::class);
		foreach ($this->unit->Region()->Estate() as $construction) {
			if ($construction->Building() === $building && $construction->Size() >= $building->UsefulSize()) {
				$effect = new Unmaintained(State::getInstance());
				if (Lemuria::Score()->find($effect->setConstruction($construction))) {
					continue;
				}
				$owner = $construction->Inhabitants()->Owner()?->Party();
				if ($owner && $owner !== $party && ($this->context->getTurnOptions()->IsSimulation() || !$owner->Diplomacy()->has(Relation::PASS, $this->unit))) {
					continue;
				}
				if ($neighbour->Landscape() instanceof Navigable) {
					if ($this->payCanalFee($construction)) {
						return true;
					}
				}
			}
		}
		return false;
	}

	protected function isNewlyExploredLand(Region $region): bool {
		return !$this->chronicle->has($region->Id()) && $this->trip->Movement() === Movement::Ship && !$region->Landscape() instanceof Navigable;
	}

	private function initNeighbourDirections(): void {
		if (empty($this->neighbourDirections)) {
			$map        = Lemuria::World();
			$directions = [];
			foreach (Direction::cases() as $direction) {
				if ($map->isDirection($direction)) {
					$directions[] = $direction;
				}
			}
			$l = count($directions) - 1;
			$this->neighbourDirections[$directions[0]->value][] = $directions[$l];
			for ($i = 1; $i <= $l; $i++) {
				$this->neighbourDirections[$directions[$i - 1]->value][] = $directions[$i];
				$this->neighbourDirections[$directions[$i]->value][] = $directions[$i - 1];
			}
			$this->neighbourDirections[$directions[$l]->value][] = $directions[0];
		}
	}

	private function payCanalFee(Construction $construction): bool {
		$extensions = $construction->Extensions();
		/** @var Fee $fee */
		$fee         = $extensions[Fee::class];
		$quantity    = $fee->Fee();
		$feePerPoint = $quantity?->Count();
		if ($feePerPoint > 0) {
			$commodity =$quantity->Commodity();
			$captain   = $this->vessel->Passengers()->Owner();
			$totalFee  = $this->vessel->Ship()->Captain() * $feePerPoint;
			$payment   = $this->collectQuantity($captain, $commodity, $totalFee);
			if ($payment->Count() < $totalFee) {
				$this->message(TravelUnpaidCanalMessage::class, $this->vessel->Passengers()->Owner());
				return false;
			}
			$captain->Inventory()->remove($payment);
			$master = $construction->Inhabitants()->Owner();
			$master->Inventory()->add(new Quantity($commodity, $totalFee));
			$this->message(TravelCanalPaidMessage::class, $captain)->i($payment);
			$this->message(TravelCanalFeeMessage::class, $master)->e($captain)->i($payment);
		}
		return true;
	}

	/**
	 * @return array<array>
	 * @noinspection DuplicatedCode
	 */
	private function chooseAlternatives(Direction $direction, Region $region): array {
		$alternatives = [];
		$count        = 0;
		$considered   = 0;
		$known        = null;
		foreach (Lemuria::World()->getAlternatives($region, $direction) as $altDirection => $altRegion) {
			$considered++;
			if ($this->canMoveTo($altDirection, false)) {
				if ($this->chronicle->has($altRegion->Id())) {
					if (!$known) {
						$known = [$altDirection, $altRegion];
					}
				} else {
					$alternatives[] = [$altDirection, $altRegion];
					$count++;
				}
			}
			if ($considered > 2 && $count) {
				break; // Use nearest alternatives if possible (octagonal maps).
			}
		}
		if (empty($alternatives) && $known) {
			return [$known];
		}
		return $alternatives;
	}

	/**
	 * @return array<array>
	 * @noinspection DuplicatedCode
	 */
	private function chooseAlternativeNavigables(Direction $direction, Region $region): array {
		$alternatives = [];
		$count        = 0;
		$considered   = 0;
		$known        = null;
		foreach (Lemuria::World()->getAlternatives($region, $direction) as $altDirection => $altRegion) {
			$considered++;
			/** @var Region $altRegion */
			if ($altRegion->Landscape() instanceof Navigable && $this->canMoveTo($altDirection, false)) {
				if ($this->chronicle->has($altRegion->Id())) {
					if (!$known) {
						$known = [$altDirection, $altRegion];
					}
				} else {
					$alternatives[] = [$altDirection, $altRegion];
					$count++;
				}
			}
			if ($considered > 2 && $count) {
				break; // Use nearest alternatives if possible (octagonal maps).
			}
		}
		if (empty($alternatives) && $known) {
			return [$known];
		}
		return $alternatives;
	}

	/**
	 * @return array<array>
	 * @noinspection DuplicatedCode
	 */
	private function chooseAlternativesToChaos(Direction $direction, Region $region, bool $allowNavigable = false): array {
		$alternatives = [];
		$count        = 0;
		$considered   = 0;
		$known        = null;
		foreach (Lemuria::World()->getAlternatives($region, $direction) as $altDirection => $altRegion) {
			/** @var Region $altRegion */
			$considered++;
			if (!$allowNavigable && $altRegion->Landscape() instanceof Navigable) {
				continue;
			}
			if ($this->canMoveTo($altDirection, false)) {
				if ($this->chronicle->has($altRegion->Id())) {
					if (!$known) {
						$known = [$altDirection, $altRegion];
					}
				} else {
					$alternatives[] = [$altDirection, $altRegion];
					$count++;
				}
			}
			if ($considered > 2 && $count) {
				break; // Use nearest alternatives if possible (octagonal maps).
			}
		}
		if (empty($alternatives) && $known) {
			return [$known];
		}
		return $alternatives;
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
