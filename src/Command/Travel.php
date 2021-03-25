<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Capacity;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\NavigationTrait;
use Lemuria\Engine\Fantasya\Message\Construction\LeaveNewOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Construction\LeaveNoOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveConstructionDebugMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelIntoChaosMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelIntoOceanMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNeighbourMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNoCrewMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNoNavigationMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelNotCaptainMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelSpeedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelTooHeayMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelRegionMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelAnchorMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelLandMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelOverLandMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\TravelShipTooHeavyMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Vessel;

/**
 * Implementation of command REISEN (travel).
 *
 * - REISEN <direction> [<direction>...]
 */
final class Travel extends UnitCommand implements Activity
{
	use NavigationTrait;

	private ?Vessel $vessel = null;

	private Capacity $capacity;

	private ?Travel $newDefault = null;

	/**
	 * Get the new default command.
	 */
	public function getNewDefault(): ?UnitCommand {
		return $this->newDefault;
	}

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
					$this->message(TravelRegionMessage::class)->e($region);
					$route[] = $region;
					$regions--;
				}
			}
		} catch (UnknownCommandException $directionError) {
		}

		if ($this->vessel) {
			foreach ($this->vessel->Passengers() as $unit /* @var Unit $unit */) {
				$this->message(TravelMessage::class, $unit)->p($movement)->entities($route);
			}
		} else {
			$this->message(TravelMessage::class)->p($movement)->entities($route);
		}
		$this->setDefaultTravel($i, $n);
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
				if (!$this->canSailTo($landscape)) {
					$this->message(TravelLandMessage::class, $this->vessel)->p($direction)->s($landscape)->e($neighbour);
					return null;
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

		if ($this->vessel) {
			$this->moveVessel($destination);
		} else {
			$region->Residents()->remove($this->unit);
			$destination->Residents()->add($this->unit);
			$this->unit->Party()->Chronicle()->add($region);
		}
	}

	protected function setDefaultTravel(int $i, int $n): void {
		if ($i < $n) {
			$travel = $this->phrase->getVerb();
			for (++$i; $i <= $n; $i++) {
				$travel .= ' ' . $this->phrase->getParameter($i);
			}
			/** @var Travel $command */
			$command          = $this->context->Factory()->create(new Phrase($travel));
			$this->newDefault = $command;
		}
	}
}
