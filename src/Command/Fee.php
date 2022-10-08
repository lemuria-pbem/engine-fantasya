<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Message\Construction\FeeNoneMessage;
use Lemuria\Engine\Fantasya\Message\Construction\FeePercentMessage;
use Lemuria\Engine\Fantasya\Message\Construction\FeeQuantityMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FeeNotApplicableMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FeeNotInsideMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FeeNotOwnerMessage;
use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Quantity;

/**
 * This command is given by a construction owner in specific buildings to set a tax fee.
 *
 * - STEUERN <amount> [<commodity>]
 * - STEUERN <number>%
 * - STEUERN <number> %
 *
 * - STEUERSATZ <amount> [<commodity>]
 * - STEUERSATZ <number>%
 * - STEUERSATZ <number> %
 */
final class Fee extends UnitCommand
{
	protected function run(): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			if ($this->unit !== $construction->Inhabitants()->Owner()) {
				$this->message(FeeNotOwnerMessage::class);
				return;
			}
			$market   = $construction->Extensions()->offsetGet(Market::class);
			$building = $construction->Building();
			if ($market instanceof Market) {
				$fee = $this->parseFee();
				$market->setFee($fee);
				if ($fee instanceof Quantity) {
					$this->message(FeeQuantityMessage::class, $construction)->s($building)->i($fee);
				} elseif (is_float($fee)) {
					$this->message(FeePercentMessage::class, $construction)->s($building)->p($fee);
				} else {
					$this->message(FeeNoneMessage::class, $construction)->s($building);
				}
			} else {
				$this->message(FeeNotApplicableMessage::class)->s($building);
			}
		} else {
			$this->message(FeeNotInsideMessage::class);
		}
	}

	private function parseFee(): Quantity|float|null {
		$n = $this->phrase->count();
		if ($n < 1) {
			throw new UnknownCommandException($this);
		}

		$param = $this->phrase->getParameter();
		if (preg_match('/^(\d+)%$/', $param, $matches) === 1) {
			return min(1.0, $matches[1] / 100.0);
		}

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

		return new Quantity($this->context->Factory()->commodity($commodity), $number);
	}
}
