<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Blockade;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\ActivityException;
use Lemuria\Engine\Fantasya\Exception\AlternativeException;
use Lemuria\Engine\Fantasya\Factory\Command\Dummy;
use Lemuria\Engine\Fantasya\Factory\Command\Simulation;
use Lemuria\Engine\Fantasya\Factory\DirectionList;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\ModifiedActivityTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Party\TravelAllowedMessage;
use Lemuria\Engine\Fantasya\Message\Party\TravelBlockMessage;
use Lemuria\Engine\Fantasya\Message\Party\TravelGuardMessage;
use Lemuria\Engine\Fantasya\Message\Region\TravelAllowedRegionMessage;
use Lemuria\Engine\Fantasya\Message\Region\TravelBlockedInMessage;
use Lemuria\Engine\Fantasya\Message\Region\TravelGuardedRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RoutePauseMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelBlockedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelExploreDepartMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelExploreLandMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelIntoMonsterMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNoCrewMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNoMoreMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNoNavigationMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNoRidingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNotCaptainMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelPassMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelRoadMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelSimulationMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelSpeedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelTooHeavyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelVesselIncompleteMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelVesselTooHeavyMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelShipTooHeavyMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\Travel\Movement;
use Lemuria\Engine\Fantasya\Travel\NavigationTrait;
use Lemuria\Engine\Fantasya\Travel\Transport;
use Lemuria\Engine\Fantasya\Travel\TravelTrait;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party\Exploring;
use Lemuria\Model\Fantasya\Spell\FavorableWinds;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\World\Direction;

/**
 * Implementation of command REISEN.
 *
 * - REISEN <direction> [<direction>...]
 */
class Travel extends UnitCommand implements Activity
{
	use ModifiedActivityTrait;
	use NavigationTrait;
	use SiegeTrait;
	use TravelTrait;

	protected DirectionList $directions;

	protected Talent $riding;

	protected bool $unitIsStopped = false;

	private static bool $poolsHaveBeenReset = false;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->chronicle  = $this->unit->Party()->Chronicle();
		$this->workload   = $context->getWorkload($this->unit);
		$this->directions = new DirectionList($context);
		$this->riding     = self::createTalent(Riding::class);
	}

	public function execute(): static {
		parent::execute();
		if ($this->hasTravelled) {
			parent::commitCommand($this);
		} else {
			if ($this->context->getTurnOptions()->IsSimulation()) {
				parent::commitCommand(new Simulation($this->phrase, $this->context));
			} else {
				parent::commitCommand(new Dummy($this->phrase, $this->context));
			}
		}
		return $this;
	}

	public function getNewDefault(): ?UnitCommand {
		if ($this->directions->hasMore()) {
			$travel = $this->phrase->getVerb() . ' ' . implode(' ', $this->directions->route());
			/** @var Travel $command */
			$command = $this->context->Factory()->create(new Phrase($travel));
			if ($this->unit->Party()->Type() === Type::Player) {
				$this->preventDefault = false;
			}
			return $command;
		}
		return null;
	}

	/**
	 * Allow execution of other activities of the same class.
	 */
	public function allows(Activity $activity): bool {
		return $activity instanceof Travel;
	}

	protected function initialize(): void {
		parent::initialize();
		if (!self::$poolsHaveBeenReset) {
			$this->context->resetResourcePools();
			self::$poolsHaveBeenReset = true;
		}
		$this->exploring = $this->unit->Party()->Presettings()->Exploring();
		$this->vessel    = $this->unit->Vessel();
		$this->trip      = $this->calculus()->getTrip();
		$this->workload->setMaximum(min($this->workload->Maximum(), $this->trip->Speed()));
		$this->initDirections();
	}

	protected function run(): void {
		if ($this->directions->count() <= 0) {
			throw new UnknownCommandException();
		}
		if (!$this->canEnterOrLeave($this->unit)) {
			$this->message(TravelSiegeMessage::class);
			return;
		}

		$movement  = $this->trip->Movement();
		$speed     = $this->trip->Speed();
		$transport = Transport::check($this->trip);
		if ($movement === Movement::Ship) {
			if ($this->vessel->Anchor() === Direction::IN_DOCK && $this->vessel->Completion() < 1.0) {
				$this->message(TravelVesselIncompleteMessage::class)->e($this->vessel);
				return;
			}
			if ($transport === Transport::TOO_HEAVY) {
				$this->message(TravelVesselTooHeavyMessage::class)->e($this->vessel);
				$this->message(TravelShipTooHeavyMessage::class, $this->vessel);
				return;
			}
			if ($this->vessel->Passengers()->Owner() !== $this->unit) {
				$this->message(TravelNotCaptainMessage::class)->e($this->vessel);
				return;
			}
			if ($this->calculus()->knowledge(Navigation::class)->Level() < $this->vessel->Ship()->Captain()) {
				$this->message(TravelNoNavigationMessage::class)->e($this->vessel);
				return;
			}
			if ($this->navigationTalent() < $this->vessel->Ship()->Crew()) {
				$this->message(TravelNoCrewMessage::class)->e($this->vessel);
				return;
			}
			if ($this->isNavigatedByAquans()) {
				$speed++;
			}
			if ($this->hasFavorableWinds()) {
				$speed += FavorableWinds::SPEED_BONUS;
			}
		} else {
			if ($transport === Transport::TOO_HEAVY) {
				$this->message(TravelTooHeavyMessage::class);
				return;
			}
			if ($transport === Transport::NO_RIDING) {
				$this->message(TravelNoRidingMessage::class);
				return;
			}
		}
		$this->setRoadsLeft($movement);

		$route              = [$this->unit->Region()];
		$regions            = $speed - $this->workload->count();
		$roadRegions        = 2 * $regions;
		$isSimulation       = $this->context->getTurnOptions()->IsSimulation();
		$numberOfDirections = $this->directions->getNumberOfDirections();
		$invalidDirections  = 0;
		$simulationStopped  = false;
		$blockade           = new Blockade();
		$this->message(TravelSpeedMessage::class)->p($regions)->p($this->trip->Weight(), TravelSpeedMessage::WEIGHT);
		try {
			while (($regions > 0 || $roadRegions > 0) && $this->directions->hasMore()) {
				$next = $this->directions->next();
				if ($next === Direction::ROUTE_STOP) {
					break;
				}
				if ($invalidDirections >= $numberOfDirections) {
					Lemuria::Log()->debug('All directions are invalid on this travel.');
					break;
				}

				if ($isSimulation && $this->stopSimulation($next)) {
					$region            = null;
					$regions           = 0;
					$roadRegions       = 0;
					$simulationStopped = true;
				} else {
					$region = $this->canMoveTo($next);
					$region = $this->considerExploration($next, $region);
				}
				if ($region) {
					$origin   = $this->unit->Region();
					$blockade = $this->unitIsBlockedByGuards($origin, $next);
					if (!$blockade->isEmpty()) {
						$this->directions->revert();
						$this->unitIsStopped = true;
						break;
					}

					$overRoad = $this->overRoad($origin, $next, $region);
					if ($overRoad) {
						if ($this->roadsLeft > 0) {
							$this->message(TravelRoadMessage::class);
						}
						$roadRegions--;
					} else {
						if ($regions <= 0 && $roadRegions >= 2) {
							$regions++;
						}
						if ($regions <= 0) {
							$this->directions->revert();
							$this->message(TravelNoMoreMessage::class);
							break;
						}
						$roadRegions -= 2;
					}
					$regions--;
					$invalidDirections = 0;

					if ($regions > 0 && $this->isNewlyExploredLand($region)) {
						if ($this->exploring === Exploring::Land) {
							$regions     = 0;
							$roadRegions = 0;
							$this->message(TravelExploreLandMessage::class);
						} elseif ($this->exploring === Exploring::Depart) {
							$this->directions->insertNext($this->getOppositeDirection($next)->value);
							$this->message(TravelExploreDepartMessage::class);
						}
					}

					$this->moveTo($region);
					$this->addToTravelRoute($next->value);
					$this->message(TravelRegionMessage::class)->e($region);
					$route[] = $region;

					$this->workload->add();
					$guards = $this->unitIsStoppedByGuards($region);
					if ($guards->count() > 0 && !$this->airshipped) {
						$notPassGuards = $this->unitIsAllowedToPass($region, $guards);
						if ($notPassGuards->count() > 0) {
							$this->workload->add($regions);
							$regions             = 0;
							$roadRegions         = 0;
							$this->unitIsStopped = true;
							// Guard message to the guards and the stopped unit.
							foreach ($notPassGuards as $party) {
								$this->message(TravelGuardMessage::class, $party)->e($region)->e($this->unit, TravelGuardMessage::UNIT);
								if ($party->Type() === Type::Monster) {
									$this->message(TravelIntoMonsterMessage::class)->e($region);
								} else {
									$this->message(TravelGuardedMessage::class)->e($region)->e($party, TravelGuardedMessage::GUARD);
									$this->message(TravelGuardedRegionMessage::class, $region)->e($this->unit)->e($party, TravelGuardedRegionMessage::PARTY);
								}
							}
						}
						if ($this->directions->hasMore()) {
							// Pass message to the region and the passed unit.
							foreach ($guards as $party) {
								if (!$notPassGuards->has($party->Id())) {
									$this->message(TravelPassMessage::class)->e($region);
									$this->message(TravelAllowedMessage::class, $party)->e($region)->e($this->unit, TravelGuardMessage::UNIT);
									$this->message(TravelAllowedRegionMessage::class, $region)->e($this->unit)->e($party, TravelGuardedRegionMessage::PARTY);
								}
							}
						}
					}
				} else {
					$invalidDirections++;
				}
			}
		} catch (UnknownCommandException $directionError) {
		}

		if (count($route) > 1) {
			if ($this->vessel) {
				foreach ($this->vessel->Passengers() as $unit) {
					$this->message(TravelMessage::class, $unit)->p($movement->name)->entities($route);
				}
			} else {
				$this->message(TravelMessage::class)->p($movement->name)->entities($route);
			}
			if ($simulationStopped) {
				$this->message(TravelSimulationMessage::class);
			}
		} else {
			if ($isSimulation) {
				if ($simulationStopped) {
					$this->message(TravelSimulationMessage::class);
				}
			} else {
				if (!$blockade->isEmpty()) {
					$parties = [];
					foreach ($blockade as $guard) {
						$parties[$guard->Party()->Id()->Id()] = $guard->Party();
					}
					foreach ($parties as $party) {
						$this->message(TravelBlockMessage::class, $party)->e($origin)->p($next->value)->e($this->unit, TravelGuardMessage::UNIT);
						$this->message(TravelBlockedInMessage::class, $origin)->e($this->unit)->e($party, TravelGuardedRegionMessage::PARTY)->p($next->value);
					}
					if ($this->vessel) {
						foreach ($this->vessel->Passengers() as $unit) {
							$this->message(TravelBlockedMessage::class, $unit)->e($origin)->p($next->value);
						}
					} else {
						$this->message(TravelBlockedMessage::class)->e($origin)->p($next->value);
					}
					$this->hasTravelled = true;
				} else {
					if ($this->vessel) {
						foreach ($this->vessel->Passengers() as $unit) {
							$this->message(RoutePauseMessage::class, $unit);
						}
					} else {
						$this->message(RoutePauseMessage::class);
					}
				}
			}
		}
		$this->newDefault = $this->getNewDefault();
		if (isset($directionError)) {
			throw $directionError;
		}
	}

	protected function commitCommand(UnitCommand $command): void {
		$protocol = $this->context->getProtocol($this->unit);
		if ($protocol->hasActivity($this)) {
			$protocol->logCurrent($command);
			if ($command->isAlternative()) {
				$this->newDefault = $this;
				$protocol->addNewDefaults($this);
				throw new AlternativeException($command);
			}
			throw new ActivityException($command);
		}
	}

	protected function initDirections(): void {
		$this->directions->set($this->phrase);
	}

	protected function addToTravelRoute(string $direction): void {
		$this->context->getTravelRoute($this->unit)->add($direction);
	}
}
