<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Effect\Hunger;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Message\Unit\SupportCharityMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SupportDonateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SupportHungerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SupportNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SupportPayMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SupportPayOnlyMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\ResourcePool;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

/**
 * Pay every unit's upkeep.
 */
final class Support extends AbstractEvent
{
	use BuilderTrait;
	use CollectTrait;
	use StatisticsTrait;

	public const int SILVER = 10;

	private Commodity $silver;

	private People $hungryUnits;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->silver      = self::createCommodity(Silver::class);
		$this->hungryUnits = new People();
	}

	protected function run(): void {
		ResourcePool::resetReservations();
		$this->pay();
		if ($this->hungryUnits->count()) {
			Lemuria::Log()->debug($this->hungryUnits->count() . ' parties have units that cannot pay their support.');
		} else {
			Lemuria::Log()->debug('All parties have paid their support.');
		}
		foreach ($this->hungryUnits as $unit) {
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
		foreach (Region::all() as $region) {
			$intelligence = $this->context->getIntelligence($region);
			foreach ($intelligence->getParties() as $party) {
				if ($party->Type() !== Type::Player) {
					continue;
				}

				/** @var Unit $unit */
				$hungry->clear();
				foreach ($intelligence->getUnits($party) as $unit) {
					if ($this->payFromInventory($unit)) {
						Lemuria::Score()->remove($this->effect($unit));
					} else {
						$hungry->add($unit);
					}
				}
				foreach ($hungry as $unit) {
					if ($this->payFromRealmFund($unit) || $this->payFromResourcePool($unit)) {
						Lemuria::Score()->remove($this->effect($unit));
					} else {
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
					$this->placeDataMetrics(Subject::Charity, $charity, $help);
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
			$this->placeDataMetrics(Subject::Support, $payed, $unit);
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
			$this->placeDataMetrics(Subject::Support, $support, $unit);
			$this->message(SupportPayMessage::class, $unit)->i($quantity);
			return true;
		}
		return false;
	}

	private function payFromResourcePool(Unit $unit): bool {
		$support = $this->support($unit);
		$this->collectQuantity($unit, $this->silver, $support);
		return $this->payOwnSupportIfPossible($unit, $support);
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	private function payFromRealmFund(Unit $unit): bool {
		$region = $unit->Region();
		$realm  = $region->Realm();
		if ($unit->Party() === $realm?->Party() && $region !== $realm->Territory()->Central()) {
			$support   = $this->support($unit);
			$inventory = $unit->Inventory();
			$ownSilver = $inventory->offsetGet($this->silver)->Count();
			$inventory->add($this->context->getRealmFund($realm)->take(new Quantity($this->silver, $support - $ownSilver)));
			return $this->payOwnSupportIfPossible($unit, $support);
		}
		return false;
	}

	private function support(Unit $unit): int {
		$support  = self::SILVER;
		$support += $unit->Construction()?->Building()?->Feed() ?? 0;
		return $unit->Size() * $support;
	}

	private function payOwnSupportIfPossible(Unit $unit, int $support): bool {
		$inventory = $unit->Inventory();
		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		if ($ownSilver >= $support) {
			$quantity = new Quantity($this->silver, $support);
			$unit->Inventory()->remove($quantity);
			$this->placeDataMetrics(Subject::Support, $support, $unit);
			$this->message(SupportPayMessage::class, $unit)->i($quantity);
			return true;
		}
		return false;
	}

	private function findBailOut(Unit $unit): Gathering {
		$we      = $unit->Party();
		$bailOut = new Gathering();
		foreach ($this->context->getIntelligence($unit->Region())->getParties() as $party) {
			if ($party !== $we && $party->Diplomacy()->has(Relation::SILVER, $unit)) {
				$bailOut->add($party);
			}
		}
		return $bailOut;
	}

	private function nextBailOut(Intelligence $intelligence, Gathering $bailOut): ?Unit {
		$party = $bailOut->random();
		$units = $intelligence->getUnits($party);
		$help  = $units->random();
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
