<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Effect\Hunger;
use Lemuria\Engine\Lemuria\Factory\CollectTrait;
use Lemuria\Engine\Lemuria\Message\Unit\SupportCharityMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SupportDonateMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SupportHungerMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SupportNothingMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SupportPayMessage;
use Lemuria\Engine\Lemuria\Message\Unit\SupportPayOnlyMessage;
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
final class Support extends AbstractEvent
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
			Lemuria::Log()->debug($this->hungryUnits->count() . ' parties have units that cannot pay their support.');
		} else {
			Lemuria::Log()->debug('All parties have payed their support.');
		}
		foreach ($this->hungryUnits as $unit /* @var Unit $unit */) {
			$hunger = $this->payCharity($unit);
			if ($hunger > 0.0) {
				if ($hunger >= 1.0) {
					$this->message(SupportNothingMessage::class, $unit);
				}
				$effect = $this->effect($unit)->setHunger($hunger);
				Lemuria::Score()->add($effect);
				$this->message(SupportHungerMessage::class, $unit);
			} else {
				Lemuria::Score()->remove($this->effect($unit));
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
					if (!$this->payFromResourcePool($unit)) {
						$this->hungryUnits->add($unit);
					}
				}
			}
		}
	}

	private function payCharity(Unit $unit): float {
		$region       = $unit->Region();
		$intelligence = $this->context->getIntelligence($region);
		$bailOut      = $this->findBailOut($unit);
		$support      = $this->support($unit);
		$inventory    = $unit->Inventory();
		$ownSilver    = $inventory->offsetGet($this->silver)->Count();
		$neededSilver = $support - $ownSilver;
		while ($neededSilver > 0 && $bailOut->count()) {
			$help = $this->nextBailOut($intelligence, $bailOut);
			if ($help) {
				$helpSilver = $this->collectQuantity($help, $this->silver, $neededSilver);
				$charity    = $helpSilver->Count();
				if ($charity > 0) {
					$help->Inventory()->remove($helpSilver);
					$inventory->add($helpSilver);
					$neededSilver -= $charity;
					$this->message(SupportDonateMessage::class, $help)->e($unit)->i($helpSilver);
					$this->message(SupportCharityMessage::class, $unit)->e($help)->i($helpSilver);
				}
			}
		}

		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		$payed     = min($ownSilver, $support);
		if ($payed > 0) {
			$quantity = new Quantity($this->silver, $payed);
			$inventory->remove($quantity);
			if ($neededSilver > 0) {
				$this->message(SupportPayOnlyMessage::class, $unit)->i($quantity);
			} else {
				$this->message(SupportPayMessage::class, $unit)->i($quantity);
			}
		}
		return ($support - $payed) / $support;
	}

	private function payFromInventory(Unit $unit): bool {
		$support = $this->support($unit);
		if ($support <= 0) {
			return true;
		}
		$inventory = $unit->Inventory();
		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		if ($ownSilver >= $support) {
			$quantity = new Quantity($this->silver, $support);
			$inventory->remove($quantity);
			$this->message(SupportPayMessage::class, $unit)->i($quantity);
			return true;
		}
		return false;
	}

	private function payFromResourcePool(Unit $unit): bool {
		$support  = $this->support($unit);
		$quantity = $this->collectQuantity($unit, $this->silver, $support);
		if ($quantity->Count() >= $support) {
			$unit->Inventory()->remove($quantity);
			$this->message(SupportPayMessage::class, $unit)->i($quantity);
			return true;
		}
		return false;
	}

	private function support(Unit $unit): int {
		$support  = self::SILVER;
		$support += $unit->Construction()?->Building()?->Feed() ?? 0;
		return $unit->Size() * $support;
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

	private function effect(Unit $unit): Hunger {
		$effect = new Hunger($this->state);
		/** @var Hunger $hunger */
		$hunger = Lemuria::Score()->find($effect->setUnit($unit));
		return $hunger ?? $effect;
	}
}
