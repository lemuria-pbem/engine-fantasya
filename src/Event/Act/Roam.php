<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use function Lemuria\getClass;
use function Lemuria\randDistribution23;
use function Lemuria\random;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Region;

/**
 * A roaming monster.
 */
class Roam implements Act
{
	use ActTrait;

	public function act(): Roam {
		$region  = $this->unit->Region();
		$regions = $this->getPossibleRegions();
		if (empty($regions)) {
			Lemuria::Log()->debug($this->unit . ' in ' . $region . ' must stay here.');
		} else {
			$regions = $this->chooseLandscape($regions);
			$target  = $regions[rand(0, count($regions) - 1)];
			if ($target === $region) {
				Lemuria::Log()->debug($this->unit . ' in ' . $region . ' roams here.');
			} else {
				$this->moveTo($target);
				Lemuria::Log()->debug($this->unit . ' in ' . $region . ' roams to ' . $target . '.');
			}
		}
		return $this;
	}

	protected function getPossibleRegions(): array {
		$regions = [];

		$environment = $this->getMonster()?->Environment();
		if (!$environment) {
			return $regions;
		}

		foreach ($environment as $landscape) {
			$neighbours[getClass($landscape)] = [];
		}

		$region = $this->unit->Region();
		foreach (Lemuria::World()->getNeighbours($region) as $neighbour /* @var Region $neighbour */) {
			$landscape = getClass($neighbour->Landscape());
			if (isset($neighbours[$landscape])) {
				$neighbours[$landscape][] = $neighbour;
			}
		}
		$landscape = getClass($region->Landscape());
		if (isset($neighbours[$landscape])) {
			$neighbours[$landscape][] = $region;
		}

		return $regions;
	}

	protected function chooseLandscape(array $regions): array {
		$random       = random();
		$distribution = randDistribution23(count($regions));
		foreach ($distribution as $i => $chance) {
			if ($random <= $chance) {
				return $regions[$i];
			}
		}
		throw new LemuriaException();
	}

	protected function moveTo(Region $region): void {
		$this->unit->Construction()?->Inhabitants()->remove($this->unit);
		$this->unit->Vessel()?->Passengers()->remove($this->unit);
		$this->unit->Region()->Residents()->remove($this->unit);
		$region->Residents()->add($this->unit);
		$this->unit->Party()->Chronicle()->add($region);
	}
}
