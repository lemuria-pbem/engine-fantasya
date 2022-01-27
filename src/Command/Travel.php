<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\ActivityException;
use Lemuria\Engine\Fantasya\Factory\Command\Dummy;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Factory\DirectionList;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Capacity;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\NavigationTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Factory\TravelTrait;
use Lemuria\Engine\Fantasya\Message\Party\TravelAllowedMessage;
use Lemuria\Engine\Fantasya\Message\Party\TravelGuardMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RoutePauseMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNoCrewMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNoNavigationMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNoRidingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNotCaptainMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelPassMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelRoadMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelSpeedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelTooHeavyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelRegionMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelShipTooHeavyMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Talent\Riding;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\World\Direction;

/**
 * Implementation of command REISEN.
 *
 * - REISEN <direction> [<direction>...]
 */
class Travel extends UnitCommand implements Activity
{
	use DefaultActivityTrait;
	use NavigationTrait;
	use SiegeTrait;
	use TravelTrait;

	protected const ACTIVITY = 'Travel';

	protected DirectionList $directions;

	protected Talent $riding;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->workload   = $context->getWorkload($this->unit);
		$this->directions = new DirectionList($context);
		$this->riding     = self::createTalent(Riding::class);
	}

	public function execute(): Action {
		parent::execute();
		if ($this->hasTravelled) {
			parent::commitCommand($this);
		} else {
			parent::commitCommand(new Dummy($this->phrase, $this->context));
			$command = $this->getNewDefault();
			if ($command) {
				$this->context->getProtocol($this->unit)->addDefault($command);
			}
		}
		return $this;
	}

	#[Pure] public function Activity(): string {
		return self::ACTIVITY;
	}

	public function getNewDefault(): ?UnitCommand {
		if ($this->directions->hasMore()) {
			$travel = $this->phrase->getVerb() . ' ' . implode(' ', $this->directions->route());
			/** @var Travel $command */
			/** @noinspection PhpUnnecessaryLocalVariableInspection */
			$command = $this->context->Factory()->create(new Phrase($travel));
			return $command;
		}
		return null;
	}

	protected function initialize(): void {
		parent::initialize();
		$this->context->resetResourcePools();
		$this->vessel   = $this->unit->Vessel();
		$this->capacity = $this->calculus()->capacity();
		$this->workload->setMaximum(min($this->workload->Maximum(), $this->capacity->Speed()));
		$this->initDirections();
	}

	/** @noinspection PhpConditionAlreadyCheckedInspection */
	protected function run(): void {
		if ($this->directions->count() <= 0) {
			throw new UnknownCommandException();
		}
		if (!$this->canEnterOrLeave($this->unit)) {
			$this->message(TravelSiegeMessage::class);
			return;
		}

		$movement = $this->capacity->Movement();
		$speed    = $this->capacity->Speed();
		$weight   = $this->capacity->Weight();
		if ($movement === Capacity::SHIP) {
			if ($weight > $this->capacity->Ride()) {
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
		} else {
			$riding = $this->Unit()->Size() * $this->calculus()->knowledge($this->riding)->Level();
			if ($weight > $this->capacity->Ride() || $riding < $this->capacity->Talent()) {
				if ($weight > $this->capacity->Walk()) {
					$this->message(TravelTooHeavyMessage::class);
					return;
				}
				if ($riding < $this->capacity->WalkingTalent()) {
					$this->message(TravelNoRidingMessage::class);
					return;
				}
				if ($movement !== Capacity::WALK) {
					$movement = Capacity::WALK;
					$speed    = $this->capacity->WalkSpeed();
					$this->workload->setMaximum(min($this->workload->Maximum(), $speed));
				}
			}
		}
		$this->setRoadsLeft($movement);

		$route   = [$this->unit->Region()];
		$regions = $speed - $this->workload->count();
		$this->message(TravelSpeedMessage::class)->p($regions)->p($weight, TravelSpeedMessage::WEIGHT);
		try {
			while ($regions > 0 && $this->directions->hasMore()) {
				$next = $this->directions->next();
				if ($next === Direction::ROUTE_STOP) {
					break;
				}

				$region = $this->canMoveTo($next);
				if ($region) {
					$overRoad  = $this->overRoad($this->unit->Region(), $next, $region);
					$this->moveTo($region);
					$this->addToTravelRoute($next->value);
					$this->message(TravelRegionMessage::class)->e($region);
					$route[] = $region;

					if ($overRoad && $this->roadsLeft > 0) {
						$this->message(TravelRoadMessage::class);
					} else {
						$regions--;
					}

					$this->workload->add();
					$guards = $this->unitIsStoppedByGuards($region);
					if ($guards) {
						$notPassGuards = $this->unitIsAllowedToPass($region, $guards);
						if ($notPassGuards) {
							$this->workload->add($regions);
							$regions = 0;
							$this->message(TravelGuardedMessage::class)->e($region);
							foreach ($guards as $party) {
								$this->message(TravelGuardMessage::class, $party)->e($region)->e($this->unit, TravelGuardMessage::UNIT);
							}
						} elseif ($this->directions->hasMore()) {
							$this->message(TravelPassMessage::class)->e($region);
							foreach ($guards as $party) {
								$this->message(TravelAllowedMessage::class, $party)->e($region)->e($this->unit, TravelGuardMessage::UNIT);
							}
						}
					}
				}
			}
		} catch (UnknownCommandException $directionError) {
		}

		if (count($route) > 1) {
			if ($this->vessel) {
				foreach ($this->vessel->Passengers() as $unit/* @var Unit $unit */) {
					$this->message(TravelMessage::class, $unit)->p($movement)->entities($route);
				}
			} else {
				$this->message(TravelMessage::class)->p($movement)->entities($route);
			}
		} else {
			if ($this->vessel) {
				foreach ($this->vessel->Passengers() as $unit/* @var Unit $unit */) {
					$this->message(RoutePauseMessage::class, $unit);
				}
			} else {
				$this->message(RoutePauseMessage::class);
			}
		}
		if (isset($directionError)) {
			throw $directionError;
		}
	}

	protected function commitCommand(UnitCommand $command): void {
		$protocol = $this->context->getProtocol($this->unit);
		if ($protocol->hasActivity($this)) {
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
