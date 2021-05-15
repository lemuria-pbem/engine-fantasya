<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainNoDemandMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainNoExperienceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\EntertainOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Party\EntertainPreventMessage;
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

	public const QUOTA = 0.05;

	private const FEE = 20;

	private int $fee = 0;

	private int $demand = 0;

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
				$this->message(EntertainNoExperienceMessage::class);
			} else {
				$this->message(EntertainGuardedMessage::class);
				foreach ($guardParties as $party) {
					$this->message(EntertainPreventMessage::class)->e($party)->e($this->unit);
				}
			}
		} else {
			$this->unit->Inventory()->add($quantity);
			if ($quantity->Count() < $this->fee || $this->demand > $this->fee) {
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
		if ($this->phrase->count() > 0) {
			$this->demand = (int)$this->phrase->getParameter();
		}
		$level = $this->calculus()->knowledge(Entertaining::class)->Level();
		if ($level > 0) {
			$this->fee = $this->unit->Size() * $level * self::FEE;
			$this->fee = $this->reduceByWorkload($this->fee);
			if ($this->demand > 0 && $this->demand < $this->fee) {
				$this->fee = $this->demand;
			}
			$silver = self::createCommodity(Silver::class);
			$this->addToWorkload($this->fee);
			$this->resources->add(new Quantity($silver, $this->fee));
		} else {
			$this->message(EntertainNoDemandMessage::class);
		}
	}
}
