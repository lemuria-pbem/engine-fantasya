<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Activity;
use Lemuria\Engine\Lemuria\Factory\DefaultActivityTrait;
use Lemuria\Engine\Lemuria\Message\Unit\EntertainGuardedMessage;
use Lemuria\Engine\Lemuria\Message\Unit\EntertainMessage;
use Lemuria\Engine\Lemuria\Message\Unit\EntertainNoDemandMessage;
use Lemuria\Engine\Lemuria\Message\Unit\EntertainNoExperienceMessage;
use Lemuria\Engine\Lemuria\Message\Unit\EntertainOnlyMessage;
use Lemuria\Engine\Lemuria\Message\Party\EntertainPreventMessage;
use Lemuria\Model\Lemuria\Commodity\Food;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Commodity\Silver;
use Lemuria\Model\Lemuria\Relation;
use Lemuria\Model\Lemuria\Talent\Entertaining;

/**
 * Implementation of command UNTERHALTEN (a Unit uses its Entertaining skill to earn Silver).
 *
 * The command increases the current unit's Silver resource.
 *
 * - UNTERHALTEN
 */
final class Entertain extends AllocationCommand implements Activity
{
	use DefaultActivityTrait;

	private const FEE = 2 * Food::PRICE;

	private const QUOTA = 0.05;

	private int $fee = 0;

	/**
	 * Get the requested resource quota that is available for allocation.
	 */
	public function getQuota(): float {
		return self::QUOTA;
	}

	protected function run(): void {
		$quantity = $this->getResource(Silver::class);
		if ($quantity->Count() <= 0) {
			$guardParties = $this->checkBeforeAllocation();
			if (empty($guardParties)) {
				$this->message(EntertainNoExperienceMessage::class);
			} else {
				$this->message(EntertainGuardedMessage::class);
				foreach ($guardParties as $party) {
					$this->message(EntertainPreventMessage::class)->e($party)->e($this->unit);
				}
			}
		} else {
			$this->unit->Inventory()->add($quantity);
			if ($quantity->Count() < $this->fee) {
				$this->message(EntertainOnlyMessage::class)->i($quantity);
			} else {
				$this->message(EntertainMessage::class)->i($quantity);
			}
		}
	}

	/**
	 * Do the check before allocation.
	 *
	 * @return Party[]
	 */
	protected function getCheckBeforeAllocation(): array {
		return $this->getCheckByAgreement(Relation::EARN);
	}

	/**
	 * Determine the demand.
	 */
	protected function createDemand(): void {
		$level = $this->calculus()->knowledge(Entertaining::class)->Level();
		if ($level > 0) {
			$this->fee = $this->unit->Size() * $level * self::FEE;
			$silver    = self::createCommodity(Silver::class);
			$this->resources->add(new Quantity($silver, $this->fee));
		} else {
			$this->message(EntertainNoDemandMessage::class);
		}
	}
}
