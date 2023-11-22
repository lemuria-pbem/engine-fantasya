<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Monster\Bear;
use Lemuria\Model\Fantasya\Commodity\Monster\Goblin;
use Lemuria\Model\Fantasya\Commodity\Monster\Kraken;
use Lemuria\Model\Fantasya\Commodity\Monster\Wolf;
use Lemuria\Model\Fantasya\Continent;
use Lemuria\Model\Fantasya\Gang;
use Lemuria\Model\Fantasya\Landscape\Desert;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Highland;
use Lemuria\Model\Fantasya\Landscape\Mountain;
use Lemuria\Model\Fantasya\Landscape\Ocean;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Landscape\Swamp;
use Lemuria\Model\Fantasya\Unit;

final class Reproduction
{
	/**
	 * @type array<string, array<string, float>>
	 */
	public final const array POPULATION = [
		Bear::class   => [Forest::class => 1.0, Highland::class => 0.2, Mountain::class => 0.4, Plain::class => 0.5],
		Goblin::class => [Desert::class => 0.5, Forest::class => 3.0, Highland::class => 2.0, Mountain::class => 1.0, Plain::class => 5.0, Swamp::class => 3.0],
		Kraken::class => [Ocean::class  => 0.125],
		Wolf::class   => [Forest::class => 0.5, Highland::class => 0.2, Mountain::class => 0.3, Plain::class => 0.4]
	];

	/**
	 * @var array<string, array<int, int>>
	 */
	private static array $population = [];

	/**
	 * @var array<string, array<int, int>>
	 */
	private static array $maximum = [];

	private float $chance = 0.0;

	private int $size = 0;

	public function __construct(private Unit $unit) {
		$race    = $unit->Race();
		$monster = $race::class;
		if (isset(self::POPULATION[$monster]) && !isset(self::$population[$monster])) {
			$state = State::getInstance();
			foreach (Continent::all() as $continent) {
				$id                              = $continent->Id()->Id();
				$population                      = $state->getPopulation($continent);
				$population                      = $population[$race]->Count();
				self::$population[$monster][$id] = $population;

				$maximum = 0;
				$scenery = $state->getScenery($continent);
				foreach (self::POPULATION[$monster] as $landscape => $rate) {
					$count    = $scenery[$landscape]->Count();
					$maximum += $rate * $count;
				}

				$maximum                      = (int)ceil($maximum);
				self::$maximum[$monster][$id] = $maximum;
				if ($population >= $maximum) {
					Lemuria::Log()->debug('No ' . $race . ' will reproduce on ' . $continent->Name() . ' - population is too big.');
				}
			}
		}
	}

	public function Chance(): float {
		$race = $this->unit->Race()::class;
		if (isset(self::POPULATION[$race])) {
			$id         = $this->unit->Region()->Continent()->Id()->Id();
			$population = self::$population[$race][$id];
			$maximum    = self::$maximum[$race][$id];
			if ($population >= $maximum) {
				return 0.0;
			}
		}
		return $this->chance;
	}

	public function Gang(): Gang {
		return new Gang($this->unit->Race(), $this->size);
	}

	public function setChance(float $chance): Reproduction {
		$this->chance = $chance;
		return $this;
	}

	public function setSize(int $size): Reproduction {
		$this->size = $size;
		return $this;
	}
}
