<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Factory\Workplaces;
use Lemuria\Engine\Lemuria\Factory\WorkplacesTrait;
use Lemuria\Engine\Lemuria\Message\Region\PopulationFeedMessage;
use Lemuria\Engine\Lemuria\Message\Region\PopulationGrowthMessage;
use Lemuria\Engine\Lemuria\Message\Region\PopulationHungerMessage;
use Lemuria\Engine\Lemuria\Message\Region\PopulationMigrantsMessage;
use Lemuria\Engine\Lemuria\Message\Region\PopulationNewMessage;
use Lemuria\Engine\Lemuria\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Commodity;
use Lemuria\Model\Lemuria\Commodity\Peasant;
use Lemuria\Model\Lemuria\Commodity\Silver;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Region;
use Lemuria\Model\Neighbours;

/**
 * Peasants work for their living and increase their silver reserve.
 */
final class Population extends AbstractEvent
{
	use WorkplacesTrait;

	public const RATE = 0.01;

	public const MIGRATION = 0.1;

	public const WEALTH = 24;

	private Workplaces $workplaces;

	private Commodity $peasant;

	private Commodity $silver;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
		$this->workplaces = new Workplaces();
		$this->peasant    = self::createCommodity(Peasant::class);
		$this->silver     = self::createCommodity(Silver::class);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::LOCATIONS) as $region /* @var Region $region */) {
			$resources = $region->Resources();
			$peasants  = $resources[$this->peasant]->Count();
			if ($peasants <= 0) {
				continue;
			}

			$workplaces = $this->getAvailableWorkplaces($region) - $peasants;
			$available  = max(0, $workplaces);
			$reserve    = $resources[$this->silver]->Count();
			$wealth     = $reserve / $peasants / Subsistence::SILVER;
			$years      = $wealth / self::WEALTH;

			$growth = $this->calculateGrowth($peasants, $available, $years);
			if ($growth > 0) {
				$quantity = new Quantity($this->peasant, $growth);
				$resources->add($quantity);
				$this->message(PopulationGrowthMessage::class, $region)->i($quantity);
			}

			$migrants = $this->calculateMigrants($peasants, $workplaces, $years);
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
					$this->distributeMigrants($migrants, $neighbours, $distribution, $destinations);
				}
			}

			$needed = $peasants * Subsistence::SILVER;
			$used   = min($needed, $reserve);
			if ($needed > $reserve) {
				$hungry = min($peasants - (int)floor($reserve / Subsistence::SILVER), $peasants - $migrants);
				if ($hungry > 0) {
					$quantity = new Quantity($this->peasant, $hungry);
					$resources->remove($quantity);
					$this->message(PopulationHungerMessage::class, $region)->i($quantity);
				}
			}
			$feedPeasants = new Quantity($this->peasant, $peasants);
			$quantity     = new Quantity($this->silver, $used);
			$resources->remove($quantity);
			$this->message(PopulationFeedMessage::class, $region)->i($feedPeasants)->i($quantity, PopulationFeedMessage::SILVER);
		}
	}

	#[Pure] private function calculateGrowth(int $peasants, int $available, float $years): int {
		$rate = self::RATE;
		if ($available > 0) {
			$rate += $years * self::RATE;
		} elseif ($years < 0.1) {
			$rate = 0.0;
		} elseif ($years < 1.0) {
			$rate /= 10;
		}
		return (int)ceil($rate * $peasants);
	}

	#[Pure] private function calculateMigrants(int $peasants, int $workplaces, float $years): int {
		$pressure = -$workplaces / $peasants;
		$migrants = $peasants * $pressure * self::MIGRATION;
		if ($migrants > 0.0) {
			$migrants /= $years;
		} else {
			$migrants *= $years;
		}
		return (int)ceil($migrants);
	}

	private function getMigrantDistribution(Neighbours $neighbours): array {
		$distribution = [];
		foreach ($neighbours->getAll() as $d => $neighbour /* @var Region $neighbour */) {
			if ($neighbour->Landscape()->Workplaces() <= 0) {
				continue;
			}
			$resources  = $neighbour->Resources();
			$peasants   = $resources[$this->peasant]->Count();
			$workplaces = $this->getAvailableWorkplaces($neighbour) - $peasants;
			if ($peasants <= 0) {
				$peasants = 1;
			}
			$years            = $resources[$this->silver]->Count() / $peasants / Subsistence::SILVER / self::WEALTH;
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
			while ($direction = key($distribution)) {
				$destination[] = $neighbours[key($distribution)];
				$amount[]      = $max - current($distribution);
				next($distribution);
			}
			$n = 5;
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
				$remaining -= $peasants;
			} else {
				break;
			}
		}
	}
}
