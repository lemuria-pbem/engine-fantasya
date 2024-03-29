<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Effect\PotionReserve;
use Lemuria\Engine\Fantasya\Effect\Unmaintained;
use Lemuria\Engine\Fantasya\Factory\WorkloadTrait;
use Lemuria\Engine\Fantasya\Message\Construction\BreedingAtLeastMessage;
use Lemuria\Engine\Fantasya\Message\Construction\BreedingFullMessage;
use Lemuria\Engine\Fantasya\Message\Construction\BreedingSuccessMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BreedingGrowthMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Animal;
use Lemuria\Model\Fantasya\Building\AbstractBreeding;
use Lemuria\Model\Fantasya\Commodity\Potion\HorseBliss;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Potion;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\RawMaterial;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Horsetaming;
use Lemuria\Model\Fantasya\Unit;

/**
 * Animals grow in breeding farms.
 */
final class Breeding extends AbstractEvent
{
	use BuilderTrait;
	use WorkloadTrait;

	private const float RATE = 0.1;

	private const int BOOST_FACTOR = 4;

	private Talent $horsetaming;

	private Unit $unit;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
		$this->context     = new Context($state);
		$this->horsetaming = self::createTalent(Horsetaming::class);
	}

	protected function run(): void {
		foreach (Construction::all() as $construction) {
			$building = $construction->Building();
			if ($building instanceof AbstractBreeding && $this->isMaintained($construction)) {
				$animal   = $building->Animal();
				$maxBreed = $construction->Size();
				$stock    = $this->countStock($construction, $animal);
				if ($stock >= $maxBreed) {
					$this->message(BreedingFullMessage::class, $construction)->s($animal);
					continue;
				}
				if ($stock < 2) {
					$this->message(BreedingAtLeastMessage::class, $construction)->s($animal);
					continue;
				}

				$cost       = $animal instanceof RawMaterial ? $animal->getCraft()->Level() : 1;
				$production = $this->calculateProduction($construction, $cost);
				$boost      = $this->getBoost($construction);
				$growth     = $this->calculateGrowth($production, $boost, $stock, $maxBreed);
				$owner      = $construction->Inhabitants()->Owner();
				$quantity  =  new Quantity($animal, $growth);
				$owner->Inventory()->add($quantity);
				$this->message(BreedingSuccessMessage::class, $construction)->i($quantity);
				$this->message(BreedingGrowthMessage::class, $owner)->i($quantity);
			}
		}
	}

	private function countStock(Construction $construction, Animal $animal): int {
		$stock = 0;
		foreach ($construction->Inhabitants() as $unit) {
			$inventory = $unit->Inventory();
			$stock    += $inventory[$animal]->Count();
		}
		return $stock;
	}

	private function calculateProduction(Construction $construction, int $cost): int {
		$production = 0;
		foreach ($construction->Inhabitants() as $unit) {
			$this->unit = $unit;
			$calculus   = $this->context->getCalculus($unit);
			$level      = $this->getProductivity($this->horsetaming, $calculus)->Level();
			if ($level >= $cost) {
				$size        = $unit->Size();
				$production += (int)floor($this->potionBoost($size) * $size * $level / $cost);
				$this->initWorkload(0);
			}
		}
		return $production;
	}

	private function getBoost(Construction $construction): int {
		$effect   = new PotionReserve($this->state);
		$existing = Lemuria::Score()->find($effect->setConstruction($construction));
		if ($existing instanceof PotionReserve) {
			/** @var Potion $potion */
			$potion = self::createCommodity(HorseBliss::class);
			return $existing->getCount($potion) * HorseBliss::HORSES;
		}
		return 0;
	}

	private function calculateGrowth(int $production, int $boost, int $stock, int $maxBreed): int {
		$boostRate   = self::RATE * ($boost > 0 ? self::BOOST_FACTOR : 1);
		$boostBorn   = (int)floor($boostRate * min($boost, $stock) / 2);
		$regularBorn = (int)floor(self::RATE * max(0, $stock - $boost) / 2);
		$maxBorn     = $regularBorn + $boostBorn;
		$growth      = $production + $boost;
		return max(1, min($maxBorn, $growth, $maxBreed));
	}

	private function isMaintained(Construction $construction): bool {
		$effect = new Unmaintained($this->state);
		return Lemuria::Score()->find($effect->setConstruction($construction)) === null;
	}
}
