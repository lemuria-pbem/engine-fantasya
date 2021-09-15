<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Landscape;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Ship\Boat;
use Lemuria\Model\Fantasya\Vessel;
use Lemuria\Model\Fantasya\Talent\Navigation;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Neighbours;

trait NavigationTrait
{
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

	#[Pure] private function canSailTo(Landscape $landscape): bool {
		if ($this->vessel->Ship() instanceof Boat) {
			return true;
		}
		return $landscape instanceof Plain || $landscape instanceof Forest || $landscape instanceof Ocean;
	}

	private function moveVessel(Region $destination): void {
		$region = $this->vessel->Region();
		$region->Fleet()->remove($this->vessel);
		$destination->Fleet()->add($this->vessel);

		if (!($destination->Landscape() instanceof Ocean)) {
			$neighbours = Lemuria::World()->getNeighbours($destination);
			$this->vessel->setAnchor($neighbours->getDirection($region));
		}

		foreach ($this->vessel->Passengers() as $unit /* @var Unit $unit */) {
			$region->Residents()->remove($unit);
			$destination->Residents()->add($unit);
			$unit->Party()->Chronicle()->add($destination);
		}
	}
}
