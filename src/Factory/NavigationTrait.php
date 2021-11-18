<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\TravelEffect;
use Lemuria\Engine\Fantasya\Message\Region\TravelVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelGuardCancelMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Quay;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Race\Aquan;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Ship\Boat;
use Lemuria\Model\Fantasya\Ship\Dragonship;
use Lemuria\Model\Fantasya\Ship\Longboat;
use Lemuria\Model\Fantasya\Vessel;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Neighbours;

trait NavigationTrait
{
	use BuilderTrait;

	protected Context $context;

	private ?Vessel $vessel = null;

	private function navigationTalent(): int {
		$talent = 0;
		foreach ($this->vessel->Passengers() as $unit /* @var Unit $unit */) {
			$talent += $unit->Size() * $this->context->getCalculus($unit)->knowledge(Navigation::class)->Level();
		}
		return $talent;
	}

	private function hasSufficientCrew(): bool {
		$ship       = $this->vessel->Ship();
		$passengers = $this->vessel->Passengers();
		$captain    = $passengers->Owner();
		$knowledge  = $this->context->getCalculus($captain)->knowledge($this->navigation)->Level();
		if ($knowledge <= $ship->Captain()) {
			return false;
		}
		return $this->navigationTalent() >= $ship->Crew();
	}

	private function getNeighbourRegions(Region $region): Neighbours {
		$neighbours = Lemuria::World()->getNeighbours($region);
		foreach (array_keys($neighbours->getAll()) as $direction) {
			if (!$neighbours[$direction]) {
				unset($neighbours[$direction]);
			}
		}
		return $neighbours;
	}

	private function getCoastline(Neighbours $neighbours): Neighbours {
		$coastlines = new Neighbours();
		foreach ($neighbours as $direction => $neighbour /* @var Region $region */) {
			if (!($region->Landscape() instanceof Ocean)) {
				$coastlines[$direction] = $neighbour;
			}
		}
		return $coastlines;
	}

	/**
	 * @noinspection PhpConditionAlreadyCheckedInspection
	 */
	private function canSailTo(Region $region): bool {
		$landscape = $region->Landscape();
		$ship      = $this->vessel->Ship();
		if ($ship instanceof Boat) {
			return true;
		}
		if ($landscape instanceof Plain || $landscape instanceof Forest || $landscape instanceof Ocean) {
			return true;
		}
		if ($ship instanceof Longboat || $ship instanceof Dragonship) {
			$calculus = $this->context->getCalculus($this->vessel->Passengers()->Owner());
			$quay     = self::createBuilding(Quay::class);
			return $calculus->canEnter($region, $quay);
		}
		return false;
	}

	private function isNavigatedByAquans(): bool {
		$passengers = $this->vessel->Passengers();
		$captain    = $passengers->Owner();
		if ($captain->Race() instanceof Aquan) {
			$points = 0;
			foreach ($passengers as $unit /* @var Unit $unit */) {
				if ($unit->Race() instanceof Aquan) {
					$level   = $this->context->getCalculus($unit)->knowledge(Navigation::class)->Level();
					$points += $unit->Size() * $level;
				}
			}
			return $points >= $this->vessel->Ship()->Crew();
		}
		return false;
	}

	private function moveVessel(Region $destination): void {
		$region = $this->vessel->Region();
		$region->Fleet()->remove($this->vessel);
		$destination->Fleet()->add($this->vessel);

		if ($destination->Landscape() instanceof Ocean) {
			$this->vessel->setAnchor(Vessel::IN_DOCK);
		} else {
			$neighbours = Lemuria::World()->getNeighbours($destination);
			$this->vessel->setAnchor($neighbours->getDirection($region));
		}

		foreach ($this->vessel->Passengers() as $unit /* @var Unit $unit */) {
			if ($unit->IsGuarding()) {
				$unit->setIsGuarding(false);
				$this->message(TravelGuardCancelMessage::class, $unit);
			}

			$region->Residents()->remove($unit);
			$destination->Residents()->add($unit);
			$this->createNavigationEffect($unit);
			$unit->Party()->Chronicle()->add($destination);
		}

		$this->message(TravelVesselMessage::class, $region)->p((string)$this->vessel);
	}

	private function createNavigationEffect(Unit $unit): void {
		$effect = new TravelEffect(State::getInstance());
		if (!Lemuria::Score()->find($effect->setUnit($unit))) {
			Lemuria::Score()->add($effect);
		}
	}

}
