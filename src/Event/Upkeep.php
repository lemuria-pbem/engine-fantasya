<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Effect\Unmaintained;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Message\Construction\UnmaintainedMessage;
use Lemuria\Engine\Fantasya\Message\Construction\UpkeepAbandonedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UpkeepCharityMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UpkeepDonateMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UpkeepNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UpkeepPayMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UpkeepPayOnlyMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Estate;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Gathering;
use Lemuria\Model\Fantasya\Intelligence;
use Lemuria\Model\Fantasya\Party;
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

	private Commodity $silver;

	private Estate $unmaintained;

	public function __construct(State $state) {
		parent::__construct($state, Action::MIDDLE);
		$this->silver       = self::createCommodity(Silver::class);
		$this->unmaintained = new Estate();
	}

	protected function run(): void {
		$this->pay();
		if ($this->unmaintained->count()) {
			Lemuria::Log()->debug($this->unmaintained->count() . ' parties cannot maintain some of their constructions.');
		} else {
			Lemuria::Log()->debug('All parties have maintained their estate.');
		}
		foreach ($this->unmaintained as $construction /* @var Construction $construction */) {
			$owner   = $construction->Inhabitants()->Owner();
			$missing = $this->payCharity($construction);
			if ($missing > 0.0) {
				if ($missing >= 1.0) {
					$this->message(UpkeepNothingMessage::class, $owner)->e($construction);
				}
				Lemuria::Score()->add($this->effect($construction));
				$this->message(UnmaintainedMessage::class, $construction);
			} else {
				Lemuria::Score()->remove($this->effect($construction));
			}
		}
	}

	private function pay(): void {
		$unmaintained = new Estate();
		foreach (Lemuria::Catalog()->getAll(Catalog::LOCATIONS) as $region /* @var Region $region */) {
			$unmaintained->clear();
			/** @var Construction $construction */
			foreach ($region->Estate() as $construction) {
				if (!$this->payFromInventory($construction)) {
					$unmaintained->add($construction);
				}
			}
			foreach ($unmaintained as $construction) {
				if (!$this->payFromResourcePool($construction)) {
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
					$inventory->add($helpSilver);
					$neededSilver -= $charity;
					$this->message(UpkeepDonateMessage::class, $help)->e($construction)->e($unit, UpkeepDonateMessage::UNIT)->i($helpSilver);
					$this->message(UpkeepCharityMessage::class, $unit)->e($help)->e($help, UpkeepCharityMessage::UNIT)->i($helpSilver);
				}
			}
		}

		$ownSilver = $inventory->offsetGet($this->silver)->Count();
		$payed     = min($ownSilver, $upkeep);
		if ($payed > 0) {
			$inventory->remove(new Quantity($this->silver, $payed));
			if ($neededSilver > 0) {
				$this->message(UpkeepPayOnlyMessage::class, $unit)->e($construction)->i($payed);
			} else {
				$this->message(UpkeepPayMessage::class, $unit)->e($construction)->i($payed);
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
			$this->message(UpkeepPayMessage::class, $unit)->e($construction)->i($quantity);
			return true;
		}
		return false;
	}

	private function payFromResourcePool(Construction $construction): bool {
		$upkeep   = $construction->Building()->Upkeep();
		$unit     = $construction->Inhabitants()->Owner();
		$quantity = $this->collectQuantity($unit, $this->silver, $upkeep);
		if ($quantity->Count() >= $upkeep) {
			$unit->Inventory()->remove($quantity);
			$this->message(UpkeepPayMessage::class, $unit)->e($construction)->i($quantity);
			return true;
		}
		return false;
	}

	private function findBailOut(Construction $construction): Gathering {
		$bailOut = new Gathering();
		foreach ($this->context->getIntelligence($construction->Region())->getParties() as $party /* @var Party $party */) {
			if ($party->Diplomacy()->has(Relation::SILVER, $construction->Inhabitants()->Owner())) {
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

	/** @noinspection PhpRedundantVariableDocTypeInspection */
	private function effect(Construction $construction): Unmaintained {
		$effect = new Unmaintained($this->state);
		/** @var Unmaintained $effect */
		/** @noinspection PhpUnnecessaryLocalVariableInspection */
		$effect = Lemuria::Score()->find($effect->setConstruction($construction));
		return $effect;
	}
}
