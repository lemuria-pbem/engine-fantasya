<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Message\Party\RecruitPreventMessage;
use Lemuria\Engine\Lemuria\Message\RecruitGuardedMessage;
use Lemuria\Engine\Lemuria\Message\RecruitLessMessage;
use Lemuria\Engine\Lemuria\Message\RecruitMessage;
use Lemuria\Engine\Lemuria\Message\RecruitPaymentMessage;
use Lemuria\Engine\Lemuria\Message\RecruitTooExpensiveMessage;
use Lemuria\Model\Lemuria\Commodity\Silver;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Commodity\Peasant;
use Lemuria\Model\Lemuria\Relation;

/**
 * Implementation of command REKRUTIEREN (recruit peasants).
 *
 * The command pays peasants and increases a unit's size.
 *
 * - REKRUTIEREN <number>
 */
final class Recruit extends AllocationCommand
{
	private int $size;

	protected function run(): void {
		$guardParties = $this->checkBeforeAllocation();
		if (empty($guardParties)) {
			$quantity = $this->getResource(Peasant::class);
			$size     = $quantity->Count();
			$payable  = $this->getMaximumPayable();
			if ($payable > 0) {
				$this->unit->setSize($this->unit->Size() + $payable);
			}
			if ($size === $this->size) {
				$this->message(RecruitMessage::class)->p($size);
			} else {
				if ($payable < $size) {
					$this->message(RecruitTooExpensiveMessage::class)->p($size);
				} else {
					$this->message(RecruitLessMessage::class)->p($size);
				}
			}
		} else {
			$this->message(RecruitGuardedMessage::class);
			foreach ($guardParties as $party) {
				$this->message(RecruitPreventMessage::class)->e($party)->e($this->unit, RecruitPreventMessage::UNIT);
			}
		}
	}

	/**
	 * Do the check before allocation.
	 */
	protected function getCheckBeforeAllocation(): array {
		return $this->getCheckByAgreement(Relation::RESOURCES);
	}

	/**
	 * Determine the demand.
	 */
	protected function createDemand(): void {
		$size = (int)$this->phrase->getParameter();
		if (!$size) {
			throw new InvalidCommandException('Invalid size "' . $size . '".');
		}
		$this->size = $size;
		$peasant    = $this->context->Factory()->commodity(Peasant::class);
		$this->resources->add(new Quantity($peasant, $size));
	}

	/**
	 * Determine the number of recruits that can be paid.
	 */
	private function getMaximumPayable(): int {
		$payable      = $this->size;
		$silver       = $this->context->Factory()->commodity(Silver::class);
		$price        = $this->unit->Party()->Race()->Recruiting();
		$inventory    = $this->unit->Inventory();
		$ownSilver    = $inventory->offsetGet($silver)->Count();
		$neededSilver = $payable * $price;
		if ($neededSilver > $ownSilver) {
			$quantity     = new Quantity($silver, $neededSilver - $ownSilver);
			$resourcePool = $this->context->getResourcePool($this->unit);
			$resourcePool->take($this->unit, $quantity);
		}
		$ownSilver = $inventory->offsetGet($silver)->Count();
		if ($ownSilver < $neededSilver) {
			$payable      = (int)floor($ownSilver / $price);
			$neededSilver = $payable * $price;
		}
		$payment = new Quantity($silver, $neededSilver);
		$this->unit->Inventory()->remove($payment);
		$this->message(RecruitPaymentMessage::class)->i($payment)->p($payable);
		return $this->size;
	}
}
