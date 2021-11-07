<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use function Lemuria\getClass;
use function Lemuria\randDistribution23;
use function Lemuria\random;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\Act\RoamHereMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\RoamMessage;
use Lemuria\Engine\Fantasya\Message\Unit\Act\RoamStayMessage;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Region;

/**
 * A roaming monster.
 */
class Roam implements Act
{
	use ActTrait;
	use MessageTrait;

	public function act(): Roam {
		$region  = $this->unit->Region();
		$regions = $this->getPossibleRegions();
		if (empty($regions)) {
			$this->message(RoamStayMessage::class, $this->unit)->e($region);
		} else {
			$regions = $this->chooseLandscape($regions);
			$target  = $regions[rand(0, count($regions) - 1)];
			if ($target === $region) {
				$this->message(RoamHereMessage::class, $this->unit)->e($region);
			} else {
				$this->moveTo($target);
				$this->message(RoamMessage::class, $this->unit)->e($region);
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

	protected function moveTo(Region $region): void {
		$this->unit->Construction()?->Inhabitants()->remove($this->unit);
		$this->unit->Vessel()?->Passengers()->remove($this->unit);
		$this->unit->Region()->Residents()->remove($this->unit);
		$region->Residents()->add($this->unit);
		$this->unit->Party()->Chronicle()->add($region);
	}
}
