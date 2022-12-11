<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use function Lemuria\randFloat;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Act\Seek;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;
use Lemuria\Engine\Fantasya\Event\Reproduction;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

class Goblin extends AbstractBehaviour
{
	protected const RATE = 0.33;

	protected const VARIANCE = 0.2;

	protected const MINIMUM = 5;

	protected const MAXIMUM = 0.01;

	protected const MAX_UNITS = 3;

	protected Reproduction $reproduction;

	public function __construct(Unit $unit) {
		parent::__construct($unit);
		$this->reproduction = new Reproduction($this->race());
	}

	/**
	 * Goblins reproduce in Spring and Fall if they are not stealing from enemies.
	 */
	public function Reproduction(): Reproduction {
		return $this->reproduction;
	}

	public function prepare(): Behaviour {
		if ($this->hasRoamEffect()) {
			return $this;
		}
		return $this->seek();
	}

	public function conduct(): Behaviour {
		if ($this->act instanceof Seek) {
			if ($this->act->Enemy()->isEmpty()) {
				$calendar = Lemuria::Calendar();
				if (in_array($calendar->Month(), [1, 5]) && $calendar->Week() === 1) {
					$size = $this->calculateReproductionSize();
					if ($size > self::MINIMUM) {
						$this->reproduction->setChance(1.0)->setSize($size);
						$this->reproduce();
						Lemuria::Log()->debug($this->unit . ' has nothing to do and reproduced.');
					}
				}
			}
		}
		return $this->pickPocketOrRoam();
	}

	protected function calculateReproductionSize(): int {
		$region     = $this->unit->Region();
		$neighbours = Lemuria::World()->getNeighbours($region);
		$peasants   = $region->Resources()[Peasant::class]->Count();
		$units      = $region->Residents()->Size();
		foreach ($neighbours as $neighbour /* @var Region $neighbour */) {
			$peasants += $neighbour->Resources()[Peasant::class]->Count();
			$units    += $neighbour->Residents()->Size();
		}

		$calculus  = new Calculus($this->unit);
		$kinsmen   = $calculus->getKinsmen();
		$relatives = $calculus->getRelatives();
		if ($kinsmen->count() + $relatives->count() < self::MAX_UNITS - 1) {
			$total    = $this->unit->Size() + $kinsmen->Size() + $relatives->Size();
			$variance = 2.0 * self::VARIANCE * randFloat() - self::VARIANCE;
			$goblins  = (int)floor(self::RATE * (1.0 + $variance) * $total);
			$maximum  = (int)floor(self::MAXIMUM * ($peasants + $units));
			return min($goblins, $maximum);
		}
		return 0;
	}
}
