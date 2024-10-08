<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Command\Operate\Carcass as Operate;
use Lemuria\Engine\Fantasya\Effect\UnicumDisintegrate;
use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Engine\Fantasya\Factory\Workplaces;
use Lemuria\Engine\Fantasya\Factory\WorkplacesTrait;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\Region\FaunaGriffineggMessage;
use Lemuria\Engine\Fantasya\Message\Region\FaunaGrowthMessage;
use Lemuria\Engine\Fantasya\Message\Region\FaunaHungerMessage;
use Lemuria\Engine\Fantasya\Message\Region\FaunaMigrantsMessage;
use Lemuria\Engine\Fantasya\Message\Region\FaunaNewMessage;
use Lemuria\Engine\Fantasya\Message\Region\FaunaPerishMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Lemuria;
use Lemuria\Model\Calendar\Season;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Animal;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Griffinegg;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Potion\HorseBliss;
use Lemuria\Model\Fantasya\Composition\Carcass;
use Lemuria\Model\Fantasya\Landscape\Desert;
use Lemuria\Model\Fantasya\Landscape\Forest;
use Lemuria\Model\Fantasya\Landscape\Glacier;
use Lemuria\Model\Fantasya\Landscape\Highland;
use Lemuria\Model\Fantasya\Landscape\Mountain;
use Lemuria\Model\Fantasya\Landscape\Plain;
use Lemuria\Model\Fantasya\Landscape\Swamp;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\TrophySource;
use Lemuria\Model\Fantasya\Unicum;
use Lemuria\Model\Neighbours;

/**
 * Animals grow or migrate to neighbour regions.
 */
final class Fauna extends AbstractEvent
{
	use GrammarTrait;
	use StatisticsTrait;
	use WorkplacesTrait;

	/**
	 * @type array<string>
	 */
	private const array ANIMAL = [Camel::class, Elephant::class, Griffin::class, Horse::class, Pegasus::class];

	/**
	 * @type array<string, array<string, float>>
	 */
	private const array RATE = [
		Camel::class    => [Desert::class  => 0.01, Highland::class => 0.005, Plain::class => 0.002, Mountain::class    => 0.002],
		Elephant::class => [Swamp::class   => 0.01, Forest::class   => 0.002, Plain::class => 0.002],
		Griffin::class  => [Glacier::class => 0.002],
		Horse::class    => [Plain::class   => 0.01, Forest::class   => 0.005, Highland::class => 0.005, Mountain::class => 0.002],
		Pegasus::class  => [Plain::class   => 0.01]
	];

	/**
	 * @type array<string, array<int, true>>
	 */
	private const array SEASON = [
		Camel::class    => [Season::Fall->value   => true, Season::Winter->value => true],
		Elephant::class => [Season::Spring->value => true, Season::Fall->value   => true, Season::Winter->value => true],
		Griffin::class  => [Season::Spring->value => true],
		Horse::class    => [Season::Spring->value => true, Season::Summer->value => true],
		Pegasus::class  => [Season::Summer->value => true]
	];

	/**
	 * @type array<string, float>
	 */
	private const array BOOST = [
		Camel::class => 0.0, Elephant::class => 0.0, Griffin::class => 0.0, Pegasus::class => 0.0,
		Horse::class => 4.0
	];

	/**
	 * @type array<string, float>
	 */
	private const array MIGRATION = [
		Camel::class   => 0.2, Elephant::class => 0.2, Griffin::class => 0.2, Horse::class => 0.2,
		Pegasus::class => 0.0
	];

	/**
	 * @type array<string, float>
	 */
	private const array PERISH_CHANCE = [
		Camel::class   => 0.01, Elephant::class => 0.01, Griffin::class => 0.01, Horse::class => 0.01,
		Pegasus::class => 0.0
	];

	private const float MAX_RATE = 0.01;

	private const float HUNGER = 0.1;

	private const float EGG_PROBABILITY = 0.25;

	private const int PERISH_BASE = 150;

	private Workplaces $workplaces;

	private Season $season;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->workplaces = new Workplaces();
		$this->season     = Lemuria::Calendar()->Season();
	}

	protected function run(): void {
		foreach (Region::all() as $region) {
			$landscape  = $region->Landscape();
			$workplaces = $this->getPlaceForGrowing($region);
			$available  = max(0, $workplaces);
			$resources  = $region->Resources();
			foreach (self::ANIMAL as $animal) {
				$count = $resources[$animal]->Count();
				if ($count <= 0) {
					continue;
				}
				$commodity    = self::createCommodity($animal);
				$rates        = self::RATE[$animal];
				$rate         = $rates[get_class($landscape)] ?? 0.0;
				$boost        = self::BOOST[$animal];
				$boostAnimals = $this->hasApplied(HorseBliss::class, $region) * HorseBliss::HORSES;
				$boostRate    = min(1.0, $boostAnimals / $count);
				if ($available > 0) {
					if (isset(self::SEASON[$animal][$this->season->value])) {
						$growth = (int)ceil((1.0 - $boostRate) * $rate * $count + $boost * $boostRate * $rate * $count);
						if ($growth > 0) {
							$quantity = new Quantity($commodity, $growth);
							$resources->add($quantity);
							$this->message(FaunaGrowthMessage::class, $region)->i($quantity);

							if ($animal === Griffin::class) {
								$egg = self::createCommodity(Griffinegg::class);
								if ($resources[$egg]->Count() < $count + $growth) {
									if (randChance(self::EGG_PROBABILITY)) {
										$quantity = new Quantity($egg, 1);
										$resources->add($quantity);
										$this->message(FaunaGriffineggMessage::class, $region)->i($quantity);
									}
								}
							}
						}
					}
				} else {
					$migration = self::MIGRATION[$animal];
					if ($migration > 0.0) {
						$migrants = $rate > 0.0 ? (int)ceil($migration * self::MAX_RATE * self::MAX_RATE / $rate * $count) : 0;
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
					} else {
						$migrants = 0;
					}

					if ($count - $migrants > 0 && $workplaces + $migrants < 0) {
						$hunger = (int)ceil(self::HUNGER * $count);
						if ($hunger > 0) {
							$quantity = new Quantity($commodity, $hunger);
							$resources->remove($quantity);
							$this->message(FaunaHungerMessage::class, $region)->i($quantity);
						}
					}
				}

				/** @var Animal $commodity */
				if (isset($hunger)) {
					$region->Treasury()->add($this->createCarcass($commodity));
				} else {
					$living = $count - ($migrants ?? 0);
					$chance = ($living / self::PERISH_BASE) * self::PERISH_CHANCE[$commodity::class];
					if (randChance($chance)) {
						$resources->remove(new Quantity($commodity));
						$region->Treasury()->add($this->createCarcass($commodity));
						$this->message(FaunaPerishMessage::class, $region)->s($commodity);
					}
				}
			}
			$this->placeMetrics(Subject::Animals, $region);
		}
	}

	private function getMigrantDistribution(Neighbours $neighbours, array $rates): array {
		$distribution = [];
		foreach ($neighbours as $direction => $neighbour) {
			/** @var Region $neighbour */
			$landscape = $neighbour->Landscape();
			$class     = get_class($landscape);
			$rate      = $rates[$class] ?? 0.0;
			if ($rate <= 0.0) {
				continue;
			}
			$workplaces       = $this->getPlaceForGrowing($neighbour) - $this->getCultivatedWorkplaces($neighbour);
			$available        = max(0, $workplaces);
			$d                = $direction->value;
			$distribution[$d] = -$rate / self::MAX_RATE * $available;
		}
		asort($distribution, SORT_NUMERIC);
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
			while (key($distribution)) {
				$destination[] = $neighbours[key($distribution)];
				$amount[]      = $max - current($distribution);
				next($distribution);
			}
			$n = count($destination);
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

	private function createCarcass(Animal $animal): Unicum {
		$unicum = new Unicum();
		$unicum->setId(Lemuria::Catalog()->nextId(Domain::Unicum));

		/** @var Carcass $carcass */
		$carcass = self::createComposition(Carcass::class);
		$carcass->setCreature($animal);
		$carcass->setInventory($this->createCarcassInventory($animal));
		$unicum->setComposition($carcass);

		$name = $this->translateSingleton($carcass, casus: Casus::Nominative) . ' '
		      . $this->combineGrammar($animal, 'ein', Casus::Genitive);
		$unicum->setName($name);

		$effect = new UnicumDisintegrate(State::getInstance());
		Lemuria::Score()->add($effect->setUnicum($unicum)->setRounds(Operate::DISINTEGRATE));
		return $unicum;
	}

	private function createCarcassInventory(Animal $animal): Resources {
		$inventory = new Resources();
		if ($animal instanceof TrophySource) {
			$trophy = $animal->Trophy();
			if ($trophy && isset(Operate::WITH_TROPHY[$animal::class])) {
				$inventory->add(new Quantity($trophy));
			}
		}
		return $inventory;
	}
}
