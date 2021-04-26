<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\DirectionList;
use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Capacity;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\NavigationTrait;
use Lemuria\Engine\Fantasya\Factory\TravelTrait;
use Lemuria\Engine\Fantasya\Message\Party\TravelGuardMessage;
use Lemuria\Engine\Fantasya\Message\Unit\RoutePauseMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNoCrewMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNoNavigationMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNotCaptainMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelRoadMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelSpeedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelTooHeayMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelRegionMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelShipTooHeavyMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Unit;

/**
 * Implementation of command REISEN and ROUTE (travel).
 *
 * - REISEN <direction> [<direction>...]
 * - ROUTE <direction>|Pause [<direction>|Pause...]
 */
class Travel extends UnitCommand implements Activity
{
	protected const ACTIVITY = 'Travel';

	use NavigationTrait;
	use TravelTrait;

	protected DirectionList $directions;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->workload   = $context->getWorkload($this->unit);
		$this->directions = new DirectionList($context);
	}

	#[Pure] public function Activity(): string {
		return self::ACTIVITY;
	}

	public function getNewDefault(): ?UnitCommand {
		if ($this->directions->hasMore()) {
			$travel = $this->phrase->getVerb() . ' ' . implode(' ', $this->directions->route());
			/** @var Travel $command */
			$command = $this->context->Factory()->create(new Phrase($travel));
			return $command;
		}
		return null;
	}

	protected function initialize(): void {
		parent::initialize();
		$this->vessel   = $this->unit->Vessel();
		$this->capacity = $this->calculus()->capacity();
		$this->workload->setMaximum(min($this->workload->Maximum(), $this->capacity->Speed()));
		$this->directions->add($this->phrase);
	}

	protected function run(): void {
		if ($this->directions->count() <= 0) {
			throw new UnknownCommandException();
		}

		$movement = $this->capacity->Movement();
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
			if ($this->calculus()->knowledge(Navigation::class) < $this->vessel->Ship()->Captain()) {
				$this->message(TravelNoNavigationMessage::class)->e($this->vessel);
				return;
			}
			if ($this->navigationTalent() < $this->vessel->Ship()->Crew()) {
				$this->message(TravelNoCrewMessage::class)->e($this->vessel);
				return;
			}
		} else {
			if ($weight > $this->capacity->Ride()) {
				if ($weight > $this->capacity->Walk()) {
					$this->message(TravelTooHeayMessage::class);
					return;
				}
				$movement = Capacity::WALK;
			}
		}
		$this->setRoadsLeft($movement);

		$route   = [$this->unit->Region()];
		$regions = $this->capacity->Speed() - $this->workload->count();
		$this->message(TravelSpeedMessage::class)->p($regions)->p($weight, TravelSpeedMessage::WEIGHT);
		try {
			while ($regions > 0 && $this->directions->hasMore()) {
				$direction = $this->directions->next();
				if ($direction === DirectionList::ROUTE_STOP) {
					break;
				}

				$region = $this->canMoveTo($direction);
				if ($region) {
					$overRoad = $this->overRoad($this->unit->Region(), $direction, $region);
					$this->moveTo($region);
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
						$this->workload->add($regions);
						$regions = 0;
						$this->message(TravelGuardedMessage::class)->e($region);
						foreach ($guards as $party) {
							$this->message(TravelGuardMessage::class, $party)->e($region)->e($this->unit, TravelGuardMessage::UNIT);
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
}
