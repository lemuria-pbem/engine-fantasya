<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Command\Create\Commodity;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\WorkloadTrait;
use Lemuria\Engine\Fantasya\Message\Construction\BreedingAtLeastMessage;
use Lemuria\Engine\Fantasya\Message\Construction\BreedingFullMessage;
use Lemuria\Engine\Fantasya\Message\Construction\BreedingSuccessMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BreedingGrowthMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Building\AbstractBreeding;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\RawMaterial;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Horsetaming;
use Lemuria\Model\Fantasya\Unit;

/**
 * Animals grow or migrate to neighbour regions.
 */
final class Breeding extends AbstractEvent
{
	use BuilderTrait;
	use WorkloadTrait;

	private const RATE = 0.1;

	private Talent $horsetaming;

	public function __construct(State $state) {
		parent::__construct($state, Priority::MIDDLE);
		$this->context     = new Context($state);
		$this->horsetaming = self::createTalent(Horsetaming::class);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::CONSTRUCTION) as $construction /* @var Construction $construction */) {
			$building = $construction->Building();
			if ($building instanceof AbstractBreeding) {
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

	private function countStock(Construction $construction, Commodity $animal): int {
		$stock = 0;
		foreach ($construction->Inhabitants() as $unit /* @var Unit $unit */) {
			$inventory = $unit->Inventory();
			$stock    += $inventory[$animal]->Count();
		}
		return $stock;
	}

	private function calculateProduction(Construction $construction, int $cost): int {
		$production = 0;
		foreach ($construction->Inhabitants() as $unit /* @var Unit $unit */) {
			$level = $this->getProductivity($this->horsetaming)->Level();
			if ($level >= $cost) {
				$size        = $unit->Size();
				$production += (int)floor($this->potionBoost($size) * $size * $level / $cost);
				$this->initWorkload(0);
			}
		}
		return $production;
	}

	private function getBoost(Construction $construction): int {
		//TODO Check for HorseBliss effect in construction.
		return 0;
	}

	private function calculateGrowth(int $production, int $boost, int $stock, int $maxBreed): int {
		$maxBorn = (int)floor($stock / 2);
		$growth  = $production + $boost;
		return max(1, min($maxBorn, $growth, $maxBreed));
	}
}
