<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Model\World\Direction;
use function Lemuria\getClass;
use function Lemuria\randDistribution23;
use function Lemuria\randElement;
use function Lemuria\randFloat;
use Lemuria\Engine\Fantasya\Effect\RoamEffect;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Location;

trait ActTrait
{
	protected Unit $unit;

	public function __construct(Behaviour $behaviour) {
		$this->unit = $behaviour->Unit();
	}

	protected function getMonster(): ?Monster {
		$race = $this->unit->Race();
		return $race instanceof Monster ? $race : null;
	}

	/**
	 * @return array<string, array>
	 */
	protected function getPossibleRegions(bool $addCurrent = true): array {
		$regions = [];

		$environment = $this->getMonster()?->Environment();
		if (!$environment) {
			return $regions;
		}

		foreach ($environment as $landscape) {
			$regions[getClass($landscape)] = [];
		}
		$region = $this->unit->Region();
		foreach (Lemuria::World()->getNeighbours($region) as $direction => $neighbour) {
			/** @var Region $neighbour */
			$landscape = getClass($neighbour->Landscape());
			if (isset($regions[$landscape])) {
				$regions[$landscape][$direction->value] = $neighbour;
			}
		}

		if ($addCurrent) {
			$landscape = getClass($region->Landscape());
			if (isset($regions[$landscape])) {
				$regions[$landscape][Direction::None->value] = $region;
			}
		}

		foreach (array_keys($regions) as $landscape) {
			if (empty($regions[$landscape])) {
				unset($regions[$landscape]);
			}
		}
		return array_values($regions);
	}

	/**
	 * @param array<string, array> $regions
	 * @return array<string, Region>
	 */
	protected function chooseLandscape(array $regions): array {
		$random       = randFloat();
		$distribution = randDistribution23(count($regions));
		foreach ($distribution as $i => $chance) {
			if ($random < $chance) {
				return $regions[$i];
			}
		}
		throw new LemuriaException();
	}

	protected function chooseRandomNeighbour(Direction &$direction): ?Location {
		$neighbours = Lemuria::World()->getNeighbours($this->unit->Region());
		$directions = $neighbours->getDirections();
		$direction  = randElement($directions);
		return $neighbours[$direction->value];
	}

	protected function moveTo(Direction $direction, Region $region): void {
		$this->unit->Construction()?->Inhabitants()->remove($this->unit);
		$this->unit->Vessel()?->Passengers()->remove($this->unit);
		$this->unit->Region()->Residents()->remove($this->unit);
		$region->Residents()->add($this->unit);
		$this->unit->Party()->Chronicle()->add($region);
		$context = new Context(State::getInstance());
		$context->getTravelRoute($this->unit)->add($direction);
	}

	protected function createRoamEffect(): void {
		$effect = new RoamEffect(State::getInstance());
		if (!Lemuria::Score()->find($effect->setUnit($this->unit))) {
			Lemuria::Score()->add($effect);
		}
	}
}
