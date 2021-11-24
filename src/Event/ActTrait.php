<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use JetBrains\PhpStorm\Pure;

use function Lemuria\getClass;
use function Lemuria\randDistribution23;
use function Lemuria\random;
use Lemuria\Engine\Fantasya\Effect\RoamEffect;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

trait ActTrait
{
	protected Unit $unit;

	public function __construct(Behaviour $behaviour) {
		$this->unit = $behaviour->Unit();
	}

	#[Pure] protected function getMonster(): ?Monster {
		$race = $this->unit->Race();
		return $race instanceof Monster ? $race : null;
	}

	protected function getPossibleRegions(): array {
		$regions = [];

		$environment = $this->getMonster()?->Environment();
		if (!$environment) {
			return $regions;
		}

		foreach ($environment as $landscape) {
			$regions[getClass($landscape)] = [];
		}

		$region = $this->unit->Region();
		foreach (Lemuria::World()->getNeighbours($region)->getAll() as $neighbour /* @var Region $neighbour */) {
			$landscape = getClass($neighbour->Landscape());
			if (isset($regions[$landscape])) {
				$regions[$landscape][] = $neighbour;
			}
		}
		$landscape = getClass($region->Landscape());
		if (isset($regions[$landscape])) {
			$regions[$landscape][] = $region;
		}

		foreach (array_keys($regions) as $landscape) {
			if (empty($regions[$landscape])) {
				unset($regions[$landscape]);
			}
		}
		return array_values($regions);
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

	protected function chooseRandomNeighbour(): ?Region {
		$neighbours = Lemuria::World()->getNeighbours($this->unit->Region())->getAll();
		if (empty($neighbours)) {
			return null;
		}
		$direction = array_rand($neighbours);
		return $neighbours[$direction];
	}

	protected function moveTo(Region $region): void {
		$this->unit->Construction()?->Inhabitants()->remove($this->unit);
		$this->unit->Vessel()?->Passengers()->remove($this->unit);
		$this->unit->Region()->Residents()->remove($this->unit);
		$region->Residents()->add($this->unit);
		$this->unit->Party()->Chronicle()->add($region);
	}

	protected function createRoamEffect(): void {
		$effect = new RoamEffect(State::getInstance());
		if (!Lemuria::Score()->find($effect->setUnit($this->unit))) {
			Lemuria::Score()->add($effect);
		}
	}
}
