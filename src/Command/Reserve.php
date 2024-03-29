<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\ReserveAllMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ReserveEverythingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ReserveInvalidMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ReserveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ReserveNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ReserveOnlyMessage;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Quantity;

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
		$count     = $this->phrase->getParameter();
		$commodity = $this->phrase->getLine(2);

		$amount = (int)$count; // RESERVIEREN <amount> <commodity>
		if ((string)$amount !== $count) {
			if (!in_array(strtolower($count), ['alle', 'alles'])) { // RESERVIEREN Alles
				$commodity = trim($count . ' ' . $commodity); // RESERVIEREN <commodity> (every piece of a commodity)
			}
			$amount = PHP_INT_MAX;
		} else {
			$amount = max(0, $amount);
		}
		if (!$commodity && $amount < PHP_INT_MAX) { // RESERVIEREN Alles <commodity>
			throw new InvalidCommandException($this, 'No resource parameter.');
		}

		$resourcePool = $this->context->getResourcePool($this->unit);
		if ($commodity) {
			if ($amount === PHP_INT_MAX) {
				$container = $this->context->Factory()->kind($commodity);
				if ($container) {
					foreach ($container->fill()->Commodities() as $commodity) {
						/** @var Commodity $commodity */
						$reserved = $resourcePool->reserve($this->unit, new Quantity($commodity, PHP_INT_MAX));
						if ($reserved->Count() > 0) {
							$this->message(ReserveAllMessage::class)->s($commodity)->i($reserved);
						}
					}
					return;
				}
			}

			$commodity = $this->context->Factory()->commodity($commodity);
			$quantity  = new Quantity($commodity, $amount);
			if ($amount > 0) {
				$reserved      = $resourcePool->reserve($this->unit, $quantity);
				$reservedCount = $reserved->Count();
				if ($reservedCount <= 0) {
					$this->message(ReserveNothingMessage::class)->s($commodity);
				} elseif ($amount === PHP_INT_MAX) {
					$this->message(ReserveAllMessage::class)->s($commodity)->i($reserved);
				} elseif ($amount > $reservedCount) {
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

	protected function checkSize(): bool {
		return true;
	}
}
