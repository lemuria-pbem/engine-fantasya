<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Model\Fantasya\Constraints\MarketKeeper;
use Lemuria\Model\Fantasya\Quantity;

/**
 * This command is given by a construction owner in specific buildings to set a tax fee.
 *
 * - STEUERN <amount> [<commodity>]
 * - STEUERN <number> %
 *
 * - STEUERSATZ <amount> [<commodity>]
 * - STEUERSATZ <number> %
 */
final class Fee extends UnitCommand
{
	protected function run(): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			if ($this->unit !== $construction->Inhabitants()->Owner()) {
				//TODO owner only
				return;
			}
			$constraints = $construction->Constraints();
			if ($constraints instanceof MarketKeeper) {
				$fee = $this->parseFee();
				$constraints->setFee($fee);
				if ($fee instanceof Quantity) {
					//TODO
				} elseif (is_float($fee)) {
					//TODO
				} else {
					//TODO no fee
				}
			} else {
				//TODO no fee
			}
		} else {
			//TODO not in construction
		}
	}

	private function parseFee(): Quantity|float|null {
		$n = $this->phrase->count();
		if ($n < 1) {
			throw new UnknownCommandException($this);
		}

		$param  = $this->phrase->getParameter();
		$number = (int)$param;
		if ((string)$number !== $param) {
			throw new UnknownCommandException($this);
		}
		if ($number === 0) {
			return null;
		}

		$commodity = $n > 1 ? $this->phrase->getLine(2) : 'Silber';
		if ($commodity === '%') {
			return min(1.0, $number / 100.0);
		}
		return new Quantity(self::createCommodity($commodity), $number);
	}
}
