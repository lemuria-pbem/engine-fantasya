<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Activity;
use Lemuria\Engine\Lemuria\Combat\Army;
use Lemuria\Engine\Lemuria\Message\Party\TaxPreventMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TaxDemandMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TaxGuardedMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TaxMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TaxNoCollectorsMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TaxNoDemandMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TaxNoExperienceMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TaxOnlyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TaxWithoutWeaponMessage;
use Lemuria\Model\Lemuria\Commodity\Food;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Commodity\Silver;
use Lemuria\Model\Lemuria\Relation;
use Lemuria\Model\Lemuria\Talent\Taxcollecting;

/**
 * Implementation of command TREIBEN (a Unit uses its Taxcollecting skill to earn Silver).
 *
 * The command increases the current unit's Silver resource.
 *
 * - TREIBEN
 */
final class Tax extends AllocationCommand implements Activity
{
	private const RATE = 2 * Food::PRICE;

	private int $rate = 0;

	private int $level = 0;

	protected function run(): void {
		$quantity = $this->getResource(Silver::class);
		if ($quantity->Count() <= 0) {
			if ($this->level <= 0) {
				$this->message(TaxNoExperienceMessage::class);
			} else {
				$guardParties = $this->checkBeforeAllocation();
				if (!empty($guardParties)) {
					$this->message(TaxGuardedMessage::class);
					foreach ($guardParties as $party) {
						$this->message(TaxPreventMessage::class, $party)->e($this->unit);
					}
				} else {
					$this->message(TaxWithoutWeaponMessage::class);
				}
			}
		} else {
			$this->unit->Inventory()->add($quantity);
			if ($quantity->Count() < $this->rate) {
				$this->message(TaxOnlyMessage::class)->i($quantity);
			} else {
				$this->message(TaxMessage::class)->i($quantity);
			}
		}
	}

	/**
	 * Do the check before allocation.
	 *
	 * @return array
	 */
	protected function getCheckBeforeAllocation(): array {
		return $this->getCheckByAgreement(Relation::EARN);
	}

	protected function createDemand(): void {
		$this->level = $this->calculus()->knowledge(Taxcollecting::class)->Level();
		if ($this->level > 0) {
			$collectors = $this->getNumberOfTaxCollectors();
			if ($collectors > 0) {
				$this->rate = $collectors * $this->level * self::RATE;
				$silver     = self::createCommodity(Silver::class);
				$this->resources->add(new Quantity($silver, $this->rate));
				$this->message(TaxDemandMessage::class)->p($collectors, TaxDemandMessage::COLLECTORS)->p($this->rate, TaxDemandMessage::RATE);
			} else {
				$this->message(TaxNoCollectorsMessage::class);
			}
		} else {
			$this->message(TaxNoDemandMessage::class);
		}
	}

	private function getNumberOfTaxCollectors(): int {
		$army = new Army($this->unit->Party());

		$collectors = 0;
		foreach ($army->add($this->unit)->Combatants() as $combatant) {
			if ($combatant->Weapon()->isGuard()) {
				$collectors += $combatant->Size();
			}
		}
		return $collectors;
	}
}
