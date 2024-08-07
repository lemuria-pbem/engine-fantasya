<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Combat\Army;
use Lemuria\Engine\Fantasya\Combat\BattlePlace;
use Lemuria\Engine\Fantasya\Combat\Combat;
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
use Lemuria\Engine\Fantasya\Message\Unit\TaxNotFightingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxWithoutDistanceWeaponMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TaxWithoutMeleeWeaponMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
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

	private const int RATE = 20;

	private int $rate = 0;

	private int $demand = 0;

	private int $level = 0;

	private int $collectors = 0;

	private ?int $threshold = null;

	public function canBeCentralized(): bool {
		return true;
	}

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
					$battleRow = $this->unit->BattleRow()->value;
					if ($battleRow <= BattleRow::Bystander->value) {
						$this->message(TaxNotFightingMessage::class);
					} else {
						if ($battleRow >= BattleRow::Careful->value) {
							$this->message(TaxWithoutMeleeWeaponMessage::class);
						} else {
							$this->message(TaxWithoutDistanceWeaponMessage::class);
						}
					}
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
			$amount = (int)$this->phrase->getParameter();
			if ($amount < 0) {
				$quota           = abs($amount);
				$this->threshold = $quota;
				$amount          = 0;
			}
			$this->demand = $amount;
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
				if (!$this->isRunCentrally) {
					$region = $this->unit->Region();
					if (!isset($quota)) {
						$quota = $this->unit->Party()->Regulation()->getQuotas($region)?->getQuota($silver)?->Threshold();
					}
					if (is_int($quota) && $quota > 0) {
						$reserve   = $region->Resources()[$silver]->Count();
						$available = max(0, $reserve - $quota);
						if ($available < $this->rate) {
							Lemuria::Log()->debug('Availability of ' . $silver . ' reduced due to quota.');
							$this->rate = $available;
							if ($this->demand > $available) {
								$this->demand = $available;
							}
						}
					}
				}

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

	protected function undoProduction(): void {
		parent::undoProduction();
		$this->undoWorkload($this->rate);
	}

	protected function getImplicitThreshold(): int|float|null {
		return $this->threshold;
	}

	private function getNumberOfTaxCollectors(): int {
		$size = 0;
		$army = new Army($this->unit->Party(), new Combat($this->context, new BattlePlace($this->unit)));
		foreach ($army->add($this->unit)->Combatants() as $combatant) {
			if ($combatant->Weapon() && $combatant->WeaponSkill()->isGuard()) {
				$size += $combatant->Size();
			}
		}
		return $size;
	}
}
