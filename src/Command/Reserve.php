<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Message\Unit\ReserveAllMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ReserveEverythingMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ReserveInvalidMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ReserveMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ReserveOnlyMessage;
use Lemuria\Model\Lemuria\Quantity;

/**
 * Implementation of command RESERVIEREN.
 *
 * The command transfers commodities from resource pool to a unit. The unit will block these resources.
 *
 * - RESERVIEREN Alles
 * - RESERVIEREN <commodity>
 * - RESERVIEREN Alles <commodity>
 * - RESERVIEREN <amount> <commodity>
 */
final class Reserve extends UnitCommand
{
	protected function run(): void {
		$count     = $this->phrase->getParameter(1);
		$commodity = $this->phrase->getParameter(2);

		$amount = (int)$count; // RESERVIEREN <amount> <commodity>
		if ((string)$amount !== $count) {
			if (strpos('alles', strtolower($count)) !== 0) { // RESERVIEREN Alles
				$commodity = $count; // RESERVIEREN <commodity> (all of commodity)
			}
			$amount = PHP_INT_MAX;
		}
		if (!$commodity && $amount < PHP_INT_MAX) { // RESERVIEREN Alles <commodity>
			throw new InvalidCommandException($this, 'No resource parameter.');
		}

		$resourcePool = $this->context->getResourcePool($this->unit);
		if ($commodity) {
			$commodity = $this->context->Factory()->commodity($commodity);
			$quantity  = new Quantity($commodity, $amount);
			if ($amount > 0) {
				$reserved = $resourcePool->reserve($this->unit, $quantity);
				if ($amount === PHP_INT_MAX) {
					$this->message(ReserveAllMessage::class)->s($commodity)->i($reserved);
				} elseif ($amount > $reserved->Count()) {
					$this->message(ReserveOnlyMessage::class)->i($reserved);
				} else {
					$this->message(ReserveMessage::class)->i($reserved);
				}
			} else {
				$this->message(ReserveInvalidMessage::class)->i($quantity);
			}
		} else {
			$resourcePool->reserveEverything($this->unit);
			$this->message(ReserveEverythingMessage::class);
		}
	}
}
