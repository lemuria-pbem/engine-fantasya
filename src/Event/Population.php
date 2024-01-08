<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Effect\DeceasedPeasants;
use Lemuria\Engine\Fantasya\Effect\Unemployment;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Factory\Workplaces;
use Lemuria\Engine\Fantasya\Factory\WorkplacesTrait;
use Lemuria\Engine\Fantasya\Message\Region\PopulationFeedMessage;
use Lemuria\Engine\Fantasya\Message\Region\PopulationGrowthMessage;
use Lemuria\Engine\Fantasya\Message\Region\PopulationHungerMessage;
use Lemuria\Engine\Fantasya\Message\Region\PopulationMigrantsMessage;
use Lemuria\Engine\Fantasya\Message\Region\PopulationNewMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building;
use Lemuria\Model\Fantasya\Building\ForesterLodge;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Commodity\Potion\PeasantJoy;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Neighbours;

/**
 * The peasant population grows or shrinks at the end of each turn.
 *
 * The number of available recruits is calculated and persisted as an effect.
 */
final class Population extends AbstractEvent
{
	use SiegeTrait;
	use StatisticsTrait;
	use WorkplacesTrait;

	public const float UNEMPLOYMENT = 5.0;

	private const float RATE = 0.01;

	private const float MIGRATION = 0.1;

	private const int WEALTH = 24;

	private const float BOOST = 10.0;

	private Workplaces $workplaces;

	private Commodity $peasant;

	private Commodity $silver;

	private Building $foresterLodge;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->workplaces    = new Workplaces();
		$this->foresterLodge = self::createBuilding(ForesterLodge::class);
		$this->peasant       = self::createCommodity(Peasant::class);
		$this->silver        = self::createCommodity(Silver::class);
	}

	protected function run(): void {
		foreach (Region::all() as $region) {
			$resources = $region->Resources();
			$peasants  = $resources[$this->peasant]->Count();
			if ($peasants <= 0) {
				$this->calculateUnemployment($region);
				continue;
			}

			$workplaces = $this->getAvailableWorkplaces($region) - $peasants;
			$work       = $region->Landscape()->Workplaces();
			$work       = $work > 0 ? min(1.0, max(-1.0, $workplaces / $work)) : -1.0;
			$available  = max(0, $workplaces);
			$reserve    = $resources[$this->silver]->Count();
			$wealth     = $reserve / $peasants / Subsistence::SILVER;
			$years      = $wealth / self::WEALTH;
			$this->placeDataMetrics(Subject::Prosperity, $years, $region);

			$hasForester = $this->checkForRegionBuilding($region, $this->foresterLodge);
			$growth      = $hasForester ? 0 : $this->calculateGrowth($peasants, $available, $years, $work, $region);
			if ($growth > 0) {
				$quantity = new Quantity($this->peasant, $growth);
				$resources->add($quantity);
				$this->message(PopulationGrowthMessage::class, $region)->i($quantity);
			}

			$migrants = $hasForester ? 0 : $this->calculateMigrants($peasants, $workplaces, $years);
			if ($migrants > 0) {
				$neighbours   = Lemuria::World()->getNeighbours($region);
				$distribution = $this->getMigrantDistribution($neighbours);
				if (!empty($distribution)) {
					$destinations = $this->getMigrationDestinations($distribution);
					if ($destinations <= 0) {
						$migrants = (int)ceil($migrants / 10);
					}
					$quantity = new Quantity($this->peasant, $migrants);
					$resources->remove($quantity);
					$this->message(PopulationMigrantsMessage::class, $region)->i($quantity);
					$this->placeDataMetrics(Subject::Migration, -$migrants, $region);
					$this->distributeMigrants($migrants, $neighbours, $distribution, $destinations);
				}
			}

			$needed = $peasants * Subsistence::SILVER;
			$used   = min($needed, $reserve);
			$hungry = 0;
			if ($needed > $reserve) {
				$hungry = min($peasants - (int)floor($reserve / Subsistence::SILVER), $peasants - $migrants);
				if ($hungry > 0) {
					$quantity = new Quantity($this->peasant, $hungry);
					$resources->remove($quantity);
					$this->addDeceasedPeasants($region, $hungry);
					$this->message(PopulationHungerMessage::class, $region)->i($quantity);
				}
			}
			$feedPeasants = new Quantity($this->peasant, $peasants);
			$quantity     = new Quantity($this->silver, $used);
			$resources->remove($quantity);
			$this->message(PopulationFeedMessage::class, $region)->i($feedPeasants)->i($quantity, PopulationFeedMessage::SILVER);

			$this->placeDataMetrics(Subject::Births, $growth + $hungry, $region);
			$this->calculateUnemployment($region, $years, $peasants, $growth, $migrants, $hungry);
		}
	}

	private function calculateGrowth(int $peasants, int $available, float $years, float $work, Region $region): int {
		$rate = self::RATE;
		if ($available > 0) {
			$rate += $years * self::RATE;
		} elseif ($years < 0.1) {
			$rate = 0.0;
		} elseif ($years < 1.0) {
			$rate /= 10;
		}
		$boostPeasants = $this->hasApplied(PeasantJoy::class, $region) * PeasantJoy::PEASANTS;
		$boost         = min(1.0, $boostPeasants / $peasants);
		$growth        = (int)ceil((1.0 - $boost) * $rate * $peasants + self::BOOST * $boost * $rate * $peasants);
		if ($work < 0) {
			$growth += (int)round($work * $peasants / 10.0);
		}
		return max(0, $growth);
	}

	private function calculateMigrants(int $peasants, int $workplaces, float $years): int {
		$pressure = -$workplaces / $peasants;
		$migrants = $peasants * $pressure * self::MIGRATION;
		if ($years < 1 / self::WEALTH) {
			$years = 1 / self::WEALTH;
		}
		if ($migrants > 0.0) {
			$migrants /= $years;
		} else {
			$migrants *= $years;
		}
		return min($peasants, (int)ceil($migrants));
	}

	private function getMigrantDistribution(Neighbours $neighbours): array {
		$distribution = [];
		foreach ($neighbours as $direction => $neighbour) {
			if ($neighbour->Landscape()->Workplaces() <= 0 || $this->checkForRegionBuilding($neighbour, $this->foresterLodge)) {
				continue;
			}
			$resources  = $neighbour->Resources();
			$peasants   = $resources[$this->peasant]->Count();
			$workplaces = $this->getAvailableWorkplaces($neighbour) - $peasants;
			if ($peasants <= 0) {
				$peasants = 1;
			}
			$years            = $resources[$this->silver]->Count() / $peasants / Subsistence::SILVER / self::WEALTH;
			$d                = $direction->value;
			$distribution[$d] = $this->calculateMigrants($peasants, $workplaces, $years);
		}
		asort($distribution);
		return $distribution;
	}

	private function getMigrationDestinations(array $distribution): int {
		$n = 0;
		foreach ($distribution as $migrants) {
			if ($migrants < 0) {
				$n++;
			} else {
				break;
			}
		}
		return $n;
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	private function distributeMigrants(int $migrants, Neighbours $neighbours, array $distribution, int $n): void {
		$amount      = [];
		$destination = [];
		if ($n > 0) {
			reset($distribution);
			for ($i = 0; $i < $n; $i++) {
				$destination[] = $neighbours[key($distribution)];
				$amount[]      = -current($distribution);
				next($distribution);
			}
		} else {
			$max = max($distribution) + 1;
			reset($distribution);
			while (key($distribution)) {
				$destination[] = $neighbours[key($distribution)];
				$amount[]      = $max - current($distribution);
				next($distribution);
			}
			$n = count($destination);
		}
		$sum = array_sum($amount);

		$remaining = $migrants;
		for ($i = 0; $i < $n; $i++) {
			/** @var Region $region */
			$region   = $destination[$i];
			$peasants = min((int)ceil($amount[$i] / $sum * $migrants), $remaining);
			if ($peasants > 0) {
				$quantity = new Quantity($this->peasant, $peasants);
				$region->Resources()->add($quantity);
				$this->message(PopulationNewMessage::class, $region)->i($quantity);
				$this->placeDataMetrics(Subject::Migration, $peasants, $region);
				$remaining -= $peasants;
			} else {
				break;
			}
		}
	}

	private function calculateUnemployment(Region $region, float $years = 0.0, int $peasants = 0, int $growth = 0, int $migrants = 0, int $hungry = 0): void {
		$unemployment = Unemployment::getFor($region);
		$percent      = self::UNEMPLOYMENT;
		$percent     -= min((2.0 * (self::UNEMPLOYMENT - 0.5)), $years) * 0.5;
		if ($growth > 0) {
			$percent /= 2.0;
		}
		if ($migrants > 0) {
			$percent *= 4.0;
		}
		$unemployed = (int)ceil(($percent / 100.0) * ($peasants + $growth - $migrants - $hungry));
		$unemployment->setPeasants($region, $unemployed);

		$this->placeDataMetrics(Subject::Joblessness, $percent, $region);
		//Lemuria::Log()->debug('Unemployment in region ' . $region->Id() . ' is ' . round($percent, 1) . '%.');
	}

	private function addDeceasedPeasants(Region $region, int $peasants): void {
		$effect   = new DeceasedPeasants($this->state);
		$existing = Lemuria::Score()->find($effect->setRegion($region));
		if ($existing instanceof DeceasedPeasants) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
		}
		$effect->setPeasants($effect->Peasants() + $peasants);
	}
}
