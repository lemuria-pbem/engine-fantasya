<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Effect\Unmaintained;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Message\Construction\UnmaintainedMessage;
use Lemuria\Engine\Fantasya\Message\Construction\UnmaintainedOvercrowdedMessage;
use Lemuria\Engine\Fantasya\Message\Construction\UpkeepAbandonedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UpkeepCharityMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UpkeepDonateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UpkeepNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UpkeepPayMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UpkeepPayOnlyMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Estate;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Unit;

/**
 * Maintain all constructions.
 */
final class Upkeep extends AbstractEvent
{
	use BuilderTrait;
	use CollectTrait;
	use StatisticsTrait;

	private Commodity $silver;

	private Estate $unmaintained;

	private Estate $overcrowded;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
		$this->silver       = self::createCommodity(Silver::class);
		$this->unmaintained = new Estate();
		$this->overcrowded  = new Estate();
	}

	protected function run(): void {
		$this->pay();
		if ($this->unmaintained->count() || $this->overcrowded->count()) {
			if ($this->overcrowded->count()) {
				Lemuria::Log()->debug($this->overcrowded->count() . ' constructions are overcrowded and not maintained.');
			}
			if ($this->unmaintained->count()) {
				Lemuria::Log()->debug($this->unmaintained->count() . ' constructions cannot be maintained by their owners.');
			}
		} else {
			Lemuria::Log()->debug('All parties have maintained their estate.');
		}
		foreach ($this->overcrowded as $construction) {
			$this->message(UnmaintainedOvercrowdedMessage::class, $construction);
			Lemuria::Score()->add($this->effect($construction));
		}
		foreach ($this->unmaintained as $construction) {
			$owner   = $construction->Inhabitants()->Owner();
			$missing = $this->payCharity($construction);
			if ($missing > 0.0) {
				if ($missing >= 1.0) {
					$this->message(UpkeepNothingMessage::class, $owner)->e($construction);
				}
				Lemuria::Score()->add($this->effect($construction));
				$this->message(UnmaintainedMessage::class, $construction);
			}
		}
	}

	private function pay(): void {
		$unmaintained = new Estate();
		foreach (Region::all() as $region) {
			$unmaintained->clear();
			foreach ($region->Estate() as $construction) {
				if ($this->isOvercrowded($construction)) {
					$this->overcrowded->add($construction);
					continue;
				}
				if (!$this->payFromInventory($construction)) {
					$unmaintained->add($construction);
				}
			}
			foreach ($unmaintained as $construction) {
				if (!$this->payFromResourcePool($construction) && !$this->payFromRealmFund($construction)) {
					$this->unmaintained->add($construction);
				}
			}
		}
	}

	private function payCharity(Construction $construction): float {
		$unit         = $construction->Inhabitants()->Owner();
		$region       = $construction->Region();
		$intelligence = $this->context->getIntelligence($region);
		$bailOut      = $this->findBailOut($construction);
		$upkeep       = $construction->Building()->Upkeep();
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
					$this->placeDataMetrics(Subject::Charity, $charity, $help);
					$inventory->add($helpSilver);
					$neededSilver -= $charity;
					$this->message(UpkeepDonateMessage::class, $help)->e($construction)->e($unit, UpkeepCharityMessage::UNIT)->i($helpSilver);
					$this->message(UpkeepCharityMessage::class, $unit)->e($help)->e($help, UpkeepCharityMessage::UNIT)->i($helpSilver);
				}
			}
		}

		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		$payed     = min($ownSilver, $upkeep);
		if ($payed > 0) {
			$payedSilver = new Quantity($this->silver, $payed);
			$inventory->remove($payedSilver);
			$this->placeDataMetrics(Subject::Maintenance, $payed, $unit);
			if ($neededSilver > 0) {
				$this->message(UpkeepPayOnlyMessage::class, $unit)->e($construction)->i($payedSilver);
			} else {
				$this->message(UpkeepPayMessage::class, $unit)->e($construction)->i($payedSilver);
			}
		}
		return ($upkeep - $payed) / $upkeep;
	}

	private function payFromInventory(Construction $construction): bool {
		$upkeep = $construction->Building()->Upkeep();
		if ($upkeep <= 0) {
			return true;
		}
		$unit = $construction->Inhabitants()->Owner();
		if (!$unit) {
			$this->message(UpkeepAbandonedMessage::class, $construction);
			return true;
		}

		$inventory = $unit->Inventory();
		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		if ($ownSilver >= $upkeep) {
			$quantity = new Quantity($this->silver, $upkeep);
			$inventory->remove($quantity);
			$this->placeDataMetrics(Subject::Maintenance, $upkeep, $unit);
			$this->message(UpkeepPayMessage::class, $unit)->e($construction)->i($quantity);
			return true;
		}
		return false;
	}

	private function payFromResourcePool(Construction $construction): bool {
		$upkeep = $construction->Building()->Upkeep();
		$unit   = $construction->Inhabitants()->Owner();
		$this->collectQuantity($unit, $this->silver, $upkeep);
		return $this->payUpkeepIfPossible($unit, $construction, $upkeep);
	}

	private function payFromRealmFund(Construction $construction): bool {
		$region = $construction->Region();
		$unit   = $construction->Inhabitants()->Owner();
		$realm  = $region->Realm();
		if ($unit->Party() === $realm?->Party() && $region !== $realm->Territory()->Central()) {
			$upkeep    = $construction->Building()->Upkeep();
			$inventory = $unit->Inventory();
			$ownSilver = $inventory->offsetGet($this->silver)->Count();
			$inventory->add($this->context->getRealmFund($realm)->take(new Quantity($this->silver, $upkeep - $ownSilver)));
			return $this->payUpkeepIfPossible($unit, $construction, $upkeep);
		}
		return false;
	}

	private function payUpkeepIfPossible(Unit $unit, Construction $construction, int $upkeep): bool {
		$inventory = $unit->Inventory();
		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		if ($ownSilver >= $upkeep) {
			$quantity = new Quantity($this->silver, $upkeep);
			$unit->Inventory()->remove($quantity);
			$this->placeDataMetrics(Subject::Maintenance, $quantity->Count(), $unit);
			$this->message(UpkeepPayMessage::class, $unit)->e($construction)->i($quantity);
			return true;
		}
		return false;
	}

	private function findBailOut(Construction $construction): Gathering {
		$owner   = $construction->Inhabitants()->Owner();
		$we      = $owner->Party();
		$bailOut = new Gathering();
		foreach ($this->context->getIntelligence($construction->Region())->getParties() as $party) {
			if ($party !== $we && $party->Diplomacy()->has(Relation::SILVER, $owner)) {
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

	private function isOvercrowded(Construction $construction): bool {
		return $construction->Size() < $construction->Inhabitants()->Size();
	}

	private function effect(Construction $construction): Unmaintained {
		$effect = new Unmaintained($this->state);
		$existing = Lemuria::Score()->find($effect->setConstruction($construction));
		return $existing instanceof Unmaintained ? $existing->addReassignment() : $effect->addReassignment();
	}
}
