<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Effect\Hunger;
use Lemuria\Engine\Lemuria\Factory\CollectTrait;
use Lemuria\Engine\Lemuria\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Commodity;
use Lemuria\Model\Lemuria\Commodity\Silver;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Gathering;
use Lemuria\Model\Lemuria\Intelligence;
use Lemuria\Model\Lemuria\Party;
use Lemuria\Model\Lemuria\People;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Region;
use Lemuria\Model\Lemuria\Relation;
use Lemuria\Model\Lemuria\Unit;

/**
 * Pay every unit's upkeep.
 */
final class Upkeep extends AbstractEvent
{
	use BuilderTrait;
	use CollectTrait;

	public const SILVER = 10;

	private Commodity $silver;

	private People $hungryUnits;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
		$this->silver      = self::createCommodity(Silver::class);
		$this->hungryUnits = new People();
	}

	protected function run(): void {
		$this->pay();
		if ($this->hungryUnits->count()) {
			Lemuria::Log()->debug($this->hungryUnits->count() . ' parties cannot pay some of their upkeep.');
		} else {
			Lemuria::Log()->debug('All parties have payed their upkeep.');
		}
		foreach ($this->hungryUnits as $unit /* @var Unit $unit */) {
			$hunger = $this->payCharity($unit);
			if ($hunger > 0.0) {
				//TODO Hunger effect
				$effect = new Hunger($this->state);
			} else {
				//TODO payed
				//TODO clear hunger
			}
		}
	}

	private function pay(): void {
		$hungry = new People();
		foreach (Lemuria::Catalog()->getAll(Catalog::LOCATIONS) as $region /* @var Region $region */) {
			$intelligence = $this->context->getIntelligence($region);
			foreach ($intelligence->getParties() as $party /* @var Party $party */) {
				/** @var Unit $unit */
				$hungry->clear();
				foreach ($intelligence->getUnits($party) as $unit) {
					if (!$this->payFromInventory($unit)) {
						$hungry->add($unit);
					}
				}
				foreach ($hungry as $unit) {
					if ($this->payFromResourcePool($unit)) {
						$hungry->remove($unit);
					}
				}
				foreach ($hungry as $unit) {
					$this->hungryUnits->add($unit);
				}
			}
		}
	}

	private function payCharity(Unit $unit): float {
		$region       = $unit->Region();
		$intelligence = $this->context->getIntelligence($region);
		$bailOut      = $this->findBailOut($unit);
		$upkeep       = $unit->Size() * self::SILVER;
		$inventory    = $unit->Inventory();
		$ownSilver    = $inventory->offsetGet($this->silver)->Count();
		$neededSilver = $upkeep - $ownSilver;
		while ($neededSilver > 0 && $bailOut->count()) {
			$help = $this->nextBailOut($intelligence, $bailOut);
			if ($help) {
				$helpSilver = $this->collectQuantity($help, $this->silver, $neededSilver);
				$charity    = $helpSilver->Count();
				if ($charity > 0) {
					$help->Inventory()->remove($helpSilver);
					$inventory->add($helpSilver);
					$neededSilver -= $charity;
					//TODO charity
				}
			}
		}

		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		$payed     = min($ownSilver, $upkeep);
		if ($payed > 0) {
			$inventory->remove(new Quantity($this->silver, $payed));
		}
		return ($upkeep - $payed) / $upkeep;
	}

	private function payFromInventory(Unit $unit): bool {
		$upkeep = $unit->Size() * self::SILVER;
		if ($upkeep <= 0) {
			return true;
		}
		$inventory = $unit->Inventory();
		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		if ($ownSilver >= $upkeep) {
			$inventory->remove(new Quantity($this->silver, $upkeep));
			//TODO payed
			return true;
		}
		return false;
	}

	private function payFromResourcePool(Unit $unit): bool {
		$upkeep   = $unit->Size() * self::SILVER;
		$quantity = $this->collectQuantity($unit, $this->silver, $upkeep);
		if ($quantity->Count() >= $upkeep) {
			$unit->Inventory()->remove($quantity);
			//TODO payed
			return true;
		}
		return false;
	}

	private function findBailOut(Unit $unit): Gathering {
		$bailOut = new Gathering();
		foreach ($this->context->getIntelligence($unit->Region())->getParties() as $party /* @var Party $party */) {
			if ($party->Diplomacy()->has(Relation::SILVER, $unit)) {
				$bailOut->add($party);
			}
		}
		return $bailOut;
	}

	private function nextBailOut(Intelligence $intelligence, Gathering $bailOut): ?Unit {
		/** @var Party $party */
		$party = $bailOut->random();
		$units = $intelligence->getUnits($party);
		/** @var Unit $help */
		$help = $units->random();
		$bailOut->remove($party);
		return $help;
	}
}
