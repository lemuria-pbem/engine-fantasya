<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainNoDemandMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainNoExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainNoPeasantsMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainNoSilverMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Party\EntertainPreventMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Talent\Entertaining;

/**
 * Implementation of command UNTERHALTEN (a Unit uses its Entertaining skill to earn Silver).
 *
 * The command increases the current unit's Silver resource.
 *
 * - UNTERHALTEN
 * - UNTERHALTEN <amount>
 */
final class Entertain extends AllocationCommand implements Activity
{
	use DefaultActivityTrait;

	public const float QUOTA = 0.05;

	private const int FEE = 20;

	private int $fee = 0;

	private int $demand = 0;

	private ?int $threshold = null;

	public function canBeCentralized(): bool {
		return true;
	}

	/**
	 * Get the requested resource quota that is available for allocation.
	 */
	public function getQuota(): float {
		return self::QUOTA;
	}

	protected function run(): void {
		parent::run();
		$quantity = $this->getResource(Silver::class);
		if ($quantity->Count() <= 0) {
			$guardParties = $this->checkBeforeAllocation();
			if (empty($guardParties)) {
				if ($this->calculus()->knowledge(Entertaining::class)->Level() > 0) {
					if ($this->hasRegionResources(self::createCommodity(Peasant::class))) {
						$this->message(EntertainNoSilverMessage::class)->e($this->unit->Region());
					} else {
						$this->message(EntertainNoPeasantsMessage::class)->e($this->unit->Region());
					}
				} else {
					$this->message(EntertainNoExperienceMessage::class);
				}
			} else {
				$this->message(EntertainGuardedMessage::class);
				foreach ($guardParties as $party) {
					$this->message(EntertainPreventMessage::class)->e($party)->e($this->unit);
				}
			}
		} else {
			$this->unit->Inventory()->add($quantity);
			if ($quantity->Count() < $this->fee || $this->demand > $this->fee) {
				if (!$this->hasRegionResources(self::createCommodity(Silver::class))) {
					if (!$this->hasRegionResources(self::createCommodity(Peasant::class))) {
						$this->message(EntertainNoPeasantsMessage::class)->e($this->unit->Region());
					} else {
						$this->message(EntertainNoSilverMessage::class)->e($this->unit->Region());
					}
				} else {
					$this->message(EntertainOnlyMessage::class)->i($quantity);
				}
			} else {
				$this->message(EntertainMessage::class)->i($quantity);
			}
		}
	}

	/**
	 * Do the check before allocation.
	 *
	 * @return array<Party>
	 */
	protected function getCheckBeforeAllocation(): array {
		return $this->getCheckByAgreement(Relation::EARN);
	}

	/**
	 * Determine the demand.
	 */
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
		$level = $this->getProductivity(Entertaining::class)->Level();
		if ($level > 0) {
			$this->fee = $this->unit->Size() * $level * self::FEE;
			$this->fee = $this->reduceByWorkload($this->fee);
			if ($this->demand > 0 && $this->demand < $this->fee) {
				$this->fee = $this->demand;
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
					if ($available < $this->fee) {
						Lemuria::Log()->debug('Availability of ' . $silver . ' reduced due to quota.');
						$this->fee = $available;
						if ($this->demand > $available) {
							$this->demand = $available;
						}
					}
				}
			}

			$this->addToWorkload($this->fee);
			$this->resources->add(new Quantity($silver, $this->fee));
		} else {
			$this->message(EntertainNoDemandMessage::class);
		}
	}

	protected function undoProduction(): void {
		parent::undoProduction();
		$this->undoWorkload($this->fee);
	}

	protected function getImplicitThreshold(): int|float|null {
		return $this->threshold;
	}
}
