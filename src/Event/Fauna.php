<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Factory\Workplaces;
use Lemuria\Engine\Lemuria\Factory\WorkplacesTrait;
use Lemuria\Engine\Lemuria\Message\Region\FaunaGriffineggMessage;
use Lemuria\Engine\Lemuria\Message\Region\FaunaGrowthMessage;
use Lemuria\Engine\Lemuria\Message\Region\FaunaHungerMessage;
use Lemuria\Engine\Lemuria\Message\Region\FaunaMigrantsMessage;
use Lemuria\Engine\Lemuria\Message\Region\FaunaNewMessage;
use Lemuria\Engine\Lemuria\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Commodity;
use Lemuria\Model\Lemuria\Commodity\Camel;
use Lemuria\Model\Lemuria\Commodity\Elephant;
use Lemuria\Model\Lemuria\Commodity\Griffin;
use Lemuria\Model\Lemuria\Commodity\Griffinegg;
use Lemuria\Model\Lemuria\Commodity\Horse;
use Lemuria\Model\Lemuria\Landscape\Desert;
use Lemuria\Model\Lemuria\Landscape\Forest;
use Lemuria\Model\Lemuria\Landscape\Glacier;
use Lemuria\Model\Lemuria\Landscape\Highland;
use Lemuria\Model\Lemuria\Landscape\Mountain;
use Lemuria\Model\Lemuria\Landscape\Plain;
use Lemuria\Model\Lemuria\Landscape\Swamp;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Region;
use Lemuria\Model\Neighbours;

/**
 * Animals grow or migrate to neighbour regions.
 */
final class Fauna extends AbstractEvent
{
	use WorkplacesTrait;

	private const ANIMAL = [Camel::class, Elephant::class, Griffin::class, Horse::class];

	private const RATE = [
		Camel::class    => [Desert::class  => 0.01, Highland::class => 0.005, Plain::class => 0.002, Mountain::class    => 0.002],
		Elephant::class => [Swamp::class   => 0.01, Forest::class   => 0.002, Plain::class => 0.002],
		Griffin::class  => [Glacier::class => 0.002],
		Horse::class    => [Plain::class   => 0.01, Forest::class   => 0.005, Highland::class => 0.005, Mountain::class => 0.002]
	];

	private const MAX_RATE = 0.01;

	private const MIGRATION = 0.2;

	private const HUNGER = 0.1;

	private const EGG_PROBABILITY = 25;

	private Workplaces $workplaces;

	public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
		$this->workplaces = new Workplaces();
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::LOCATIONS) as $region /* @var Region $region */) {
			$landscape  = $region->Landscape();
			$workplaces = $this->getAvailableWorkplaces($region) - $this->getCultivatedWorkplaces($region);
			$available  = max(0, $workplaces);
			$resources  = $region->Resources();
			foreach (self::ANIMAL as $animal) {
				$count = $resources[$animal]->Count();
				if ($count <= 0) {
					continue;
				}
				$commodity = self::createCommodity($animal);
				$rates     = self::RATE[$animal];
				$rate      = $rates[get_class($landscape)] ?? 0.0;
				if ($available > 0) {
					$growth = (int)ceil($rate * $count);
					if ($growth > 0) {
						$quantity = new Quantity($commodity, $growth);
						$resources->add($quantity);
						$this->message(FaunaGrowthMessage::class, $region)->i($quantity);

						if ($animal === Griffin::class) {
							$egg = self::createCommodity(Griffinegg::class);
							if ($resources[$egg]->Count() < $count + $growth) {
								if (rand(0, 100) < self::EGG_PROBABILITY) {
									$quantity = new Quantity($egg, 1);
									$resources->add($quantity);
									$this->message(FaunaGriffineggMessage::class, $region)->i($quantity);
								}
							}
						}
					}
				} else {
					$migrants = (int)ceil(self::MIGRATION * self::MAX_RATE * self::MAX_RATE / $rate * $count);
					if ($migrants > 0) {
						$neighbours   = Lemuria::World()->getNeighbours($region);
						$distribution = $this->getMigrantDistribution($neighbours, $rates);
						if (!empty($distribution)) {
							$destinations = $this->getMigrationDestinations($distribution);
							if ($destinations <= 0) {
								$migrants = (int)ceil($migrants / 10);
							}
							$quantity = new Quantity($commodity, $migrants);
							$resources->remove($quantity);
							$this->message(FaunaMigrantsMessage::class, $region)->i($quantity);
							$this->distributeMigrants($quantity, $neighbours, $distribution, $destinations);
						}
					}

					if ($workplaces + $migrants < 0) {
						$hunger = (int)ceil(self::HUNGER * $count);
						if ($hunger > 0) {
							$quantity = new Quantity($commodity, $hunger);
							$resources->remove($quantity);
							$this->message(FaunaHungerMessage::class, $region)->i($quantity);
						}
					}
				}
			}
		}
	}

	private function getMigrantDistribution(Neighbours $neighbours, array $rates): array {
		$distribution = [];
		foreach ($neighbours->getAll() as $d => $neighbour /* @var Region $neighbour */) {
			$landscape = $neighbour->Landscape();
			$class     = get_class($landscape);
			$rate      = $rates[$class] ?? 0.0;
			if ($rate <= 0.0) {
				continue;
			}
			$workplaces       = $this->getAvailableWorkplaces($neighbour) - $this->getCultivatedWorkplaces($neighbour);
			$available        = max(0, $workplaces);
			$distribution[$d] = -$rate / self::MAX_RATE * $available;
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

	private function distributeMigrants(Quantity $quantity, Neighbours $neighbours, array $distribution, int $n): void {
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

		/** @var Commodity $animal */
		$animal    = $quantity->getObject();
		$migrants  = $quantity->Count();
		$remaining = $migrants;
		for ($i = 0; $i < $n; $i++) {
			/** @var Region $region */
			$region  = $destination[$i];
			$animals = min((int)ceil($amount[$i] / $sum * $migrants), $remaining);
			if ($animals > 0) {
				$quantity = new Quantity($animal, $animals);
				$region->Resources()->add($quantity);
				$this->message(FaunaNewMessage::class, $region)->i($quantity);
				$remaining -= $animals;
			} else {
				break;
			}
		}
	}
}
