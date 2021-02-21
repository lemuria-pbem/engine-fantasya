<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Activity;
use Lemuria\Engine\Lemuria\Capacity;
use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
use Lemuria\Engine\Lemuria\Message\Construction\LeaveNewOwnerMessage;
use Lemuria\Engine\Lemuria\Message\Construction\LeaveNoOwnerMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LeaveConstructionDebugMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TravelIntoChaosMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TravelIntoOceanMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TravelMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TravelNeighbourMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TravelNoCrewMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TravelNoNavigationMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TravelNotCaptainMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TravelSpeedMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TravelTooHeayMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TravelRegionMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\TravelAnchorMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\TravelLandMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\TravelOverLandMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\TravelShipTooHeavyMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Lemuria\Landscape\Forest;
use Lemuria\Model\Lemuria\Landscape\Ocean;
use Lemuria\Model\Lemuria\Landscape\Plain;
use Lemuria\Model\Lemuria\Region;
use Lemuria\Model\Lemuria\Ship\Boat;
use Lemuria\Model\Lemuria\Talent\Navigation;
use Lemuria\Model\Lemuria\Unit;
use Lemuria\Model\Lemuria\Vessel;

/**
 * Implementation of command REISEN (travel).
 *
 * - REISEN <direction> [<direction>...]
 */
final class Travel extends UnitCommand implements Activity
{
	private ?Vessel $vessel = null;

	private Capacity $capacity;

	protected function initialize(): void {
		parent::initialize();
		$this->vessel   = $this->unit->Vessel();
		$this->capacity = $this->calculus()->capacity();

	}

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n <= 0) {
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

		$route   = [$this->unit->Region()];
		$i       = 0;
		$regions = $this->capacity->Speed();
		$this->message(TravelSpeedMessage::class)->p($regions)->p($weight, TravelSpeedMessage::WEIGHT);
		try {
			while ($regions > 0 && $i < $n) {
				$direction = $this->context->Factory()->direction($this->phrase->getParameter(++$i));
				$region = $this->canMoveTo($direction);
				if ($region) {
					$this->moveTo($region);
					$route[] = [$region];
					$regions--;
					$this->message(TravelRegionMessage::class)->e($region);
				}
			}
		} catch (UnknownCommandException $directionError) {
		}

		$this->message(TravelMessage::class)->p($movement)->entities($route);
		if (isset($directionError)) {
			throw $directionError;
		}
	}

	protected function canMoveTo(string $direction): ?Region {
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
			if ($anchor !== Vessel::IN_DOCK) {
				if ($direction !== $anchor) {
					$this->message(TravelAnchorMessage::class)->p($direction)->p($anchor, TravelAnchorMessage::ANCHOR);
					return null;
				}
			}
			if ($landscape instanceof Ocean) {
				$this->message(TravelNeighbourMessage::class)->p($direction)->s($landscape)->e($neighbour);
				return $neighbour;
			}
			if ($region->Landscape() instanceof Ocean) {
				if (!($this->vessel->Ship() instanceof Boat)) {
					if (!($landscape instanceof Plain || $landscape instanceof Forest)) {
						$this->message(TravelLandMessage::class, $this->vessel)->p($direction)->s($landscape)->e($neighbour);
						return null;
					}
				}
				$this->message(TravelNeighbourMessage::class)->p($direction)->s($landscape)->e($neighbour);
				return $neighbour;
			}
			$this->message(TravelOverLandMessage::class, $this->vessel)->p($direction);
			return null;
		}

		if ($this->capacity->Movement() === Capacity::FLY) {
			$this->message(TravelNeighbourMessage::class)->p($direction)->s($landscape)->e($neighbour);
			return $neighbour;
		}

		if ($landscape instanceof Ocean) {
			$this->message(TravelIntoOceanMessage::class)->p($direction);
			return null;
		}
		$this->message(TravelNeighbourMessage::class)->p($direction)->s($landscape)->e($neighbour);
		return $neighbour;
	}

	protected function moveTo(Region $destination): void {
		$region = $this->unit->Region();

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

		$region->Residents()->remove($this->unit);
		$destination->Residents()->add($this->unit);

		$vessel = $this->unit->Vessel();
		if ($vessel) {
			$region->Fleet()->remove($vessel);
			$destination->Fleet()->add($vessel);
		}
	}

	protected function navigationTalent(): int {
		$talent = 0;
		foreach ($this->vessel->Passengers() as $unit /* @var Unit $unit */) {
			$talent += $unit->Size() * $this->context->getCalculus($unit)->knowledge(Navigation::class);
		}
		return $talent;
	}
}
