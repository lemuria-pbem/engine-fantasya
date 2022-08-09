<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Behaviour\Monster;

use function Lemuria\random;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Event\Act\Seek;
use Lemuria\Engine\Fantasya\Event\Behaviour;
use Lemuria\Engine\Fantasya\Event\Behaviour\AbstractBehaviour;
use Lemuria\Engine\Fantasya\Event\Reproduction;
use Lemuria\Engine\Fantasya\Factory\Model\Season;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

class Goblin extends AbstractBehaviour
{
	protected const VARIANCE = 0.2;

	protected const MINIMUM = 5;

	protected const MAXIMUM = 0.01;

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
				if (in_array(Lemuria::Calendar()->Season(), [Season::SPRING, Season::FALL])) {
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

		$calculus = new Calculus($this->unit);
		$total    = $this->unit->Size() + $calculus->getKinsmen()->Size() + $calculus->getRelatives()->Size();
		$factor   = 2.0 * self::VARIANCE * random() - self::VARIANCE;
		$goblins  = (1.0 + $factor) * $total;
		$maximum  = (int)floor(self::MAXIMUM * ($peasants + $units));
		return min($goblins, $maximum);
	}
}
