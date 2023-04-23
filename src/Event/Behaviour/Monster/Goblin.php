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
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin as Model;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Unit;

class Goblin extends AbstractBehaviour
{
	use BuilderTrait;

	protected const RATE = 0.33;

	protected const VARIANCE = 0.2;

	protected const MINIMUM = 5;

	protected const MAXIMUM = 0.01;

	protected const MAX_UNITS = 3;

	protected const SCATTER_UNITS = 3;

	protected const SCATTER_PERSONS = 31;

	protected const GLOBAL_RATE = 0.33;

	protected const PERISH = 0.01;

	protected static ?int $globalMaximum = null;

	protected Reproduction $reproduction;

	public function __construct(Unit $unit) {
		parent::__construct($unit);
		$this->reproduction = new Reproduction($this->race());
		if (self::$globalMaximum === null) {
			$this->initGlobalMaximum();
		}
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
		$this->perishByChance(self::PERISH);
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

	public function finish(): Behaviour {
		parent::finish();
		return $this->scatter(self::SCATTER_UNITS, self::SCATTER_PERSONS);
	}

	protected function initGlobalMaximum(): void {
		$goblins = 0;
		$players = 0;
		$race    = self::createMonster(Model::class);
		foreach (Party::all() as $party) {
			$type = $party->Type();
			if ($type === Type::Player) {
				$players += $party->People()->Size();
			} elseif ($type === Type::Monster) {
				foreach ($party->People() as $unit) {
					if ($unit->Race() === $race) {
						$goblins += $unit->Size();
					}
				}
			}
		}
		self::$globalMaximum = (int)round($players * self::GLOBAL_RATE) - $goblins;
	}

	protected function calculateReproductionSize(): int {
		$region     = $this->unit->Region();
		$neighbours = Lemuria::World()->getNeighbours($region);
		$peasants   = $region->Resources()[Peasant::class]->Count();
		$units      = $region->Residents()->Size();
		foreach ($neighbours as $neighbour) {
			$peasants += $neighbour->Resources()[Peasant::class]->Count();
			$units    += $neighbour->Residents()->Size();
		}

		$calculus  = new Calculus($this->unit);
		$kinsmen   = $calculus->getKinsmen();
		$relatives = $calculus->getRelatives();
		if ($kinsmen->count() + $relatives->count() < self::MAX_UNITS - 1) {
			$total     = $this->unit->Size() + $kinsmen->Size() + $relatives->Size();
			$variance  = 2.0 * self::VARIANCE * randFloat() - self::VARIANCE;
			$goblins   = (int)floor(self::RATE * (1.0 + $variance) * $total);
			$maximum   = (int)floor(self::MAXIMUM * ($peasants + $units));
			$reproduce = min($goblins, $maximum);
			if ($reproduce <= self::$globalMaximum) {
				return $reproduce;
			}
		}
		return 0;
	}
}
