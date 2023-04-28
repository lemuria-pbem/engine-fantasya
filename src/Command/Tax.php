<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Message\Party\TaxPreventMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxDemandMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxNoCollectorsMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxNoDemandMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxNoExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxNoPeasantsMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxNoSilverMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxWithoutWeaponMessage;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent\Taxcollecting;

/**
 * Implementation of command TREIBEN (a Unit uses its Taxcollecting skill to earn Silver).
 *
 * The command increases the current unit's Silver resource.
 *
 * - TREIBEN
 * - TREIBEN <amount>
 */
final class Tax extends AllocationCommand implements Activity
{
	use DefaultActivityTrait;

	private const RATE = 20;

	private int $rate = 0;

	private int $demand = 0;

	private int $level = 0;

	private int $collectors = 0;

	protected function run(): void {
		parent::run();
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
				} elseif ($this->collectors > 0) {
					$silver = self::createCommodity(Silver::class);
					if (!$this->hasRegionResources($silver)) {
						if (!$this->hasRegionResources(self::createCommodity(Peasant::class))) {
							$this->message(TaxNoPeasantsMessage::class)->e($this->unit->Region());
						} else {
							$this->message(TaxNoSilverMessage::class)->e($this->unit->Region());
						}
					} else {
						$this->message(TaxOnlyMessage::class)->i(new Quantity($silver, 0));
					}
				} else {
					$this->message(TaxWithoutWeaponMessage::class);
				}
			}
		} else {
			$this->unit->Inventory()->add($quantity);
			if ($quantity->Count() < $this->rate || $this->demand > $this->rate) {
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
		if ($this->phrase->count() > 0) {
			$this->demand = (int)$this->phrase->getParameter();
		}
		$this->level = $this->getProductivity(Taxcollecting::class)->Level();
		if ($this->level > 0) {
			$this->collectors = $this->getNumberOfTaxCollectors();
			if ($this->collectors > 0) {
				$this->rate = $this->collectors * $this->level * self::RATE;
				$this->rate = $this->reduceByWorkload($this->rate);
				if ($this->demand > 0 && $this->demand < $this->rate) {
					$this->rate = $this->demand;
				}
				$silver = self::createCommodity(Silver::class);
				$this->addToWorkload($this->rate);
				$this->resources->add(new Quantity($silver, $this->rate));
				$this->message(TaxDemandMessage::class)->p($this->collectors, TaxDemandMessage::COLLECTORS)->p($this->rate, TaxDemandMessage::RATE);
			} else {
				$this->message(TaxNoCollectorsMessage::class);
			}
		} else {
			$this->message(TaxNoDemandMessage::class);
		}
	}

	private function getNumberOfTaxCollectors(): int {
		foreach ($this->calculus()->weaponSkill() as $skill) {
			if ($skill->isGuard()) {
				return $this->unit->Size();
			}
		}
		return 0;
	}
}
