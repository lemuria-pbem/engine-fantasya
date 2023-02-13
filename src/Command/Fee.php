<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Message\Construction\DutyMessage;
use Lemuria\Engine\Fantasya\Message\Construction\FeeNoneMessage;
use Lemuria\Engine\Fantasya\Message\Construction\FeePercentMessage;
use Lemuria\Engine\Fantasya\Message\Construction\FeeQuantityMessage;
use Lemuria\Engine\Fantasya\Message\Construction\VesselFeeQuantityMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FeeNotApplicableMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FeeNotInsideMessage;
use Lemuria\Engine\Fantasya\Message\Unit\FeeNotOwnerMessage;
use Lemuria\Model\Fantasya\Building\Market;
use Lemuria\Model\Fantasya\Building\Port;
use Lemuria\Model\Fantasya\Building\Quay;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Extension\Duty;
use Lemuria\Model\Fantasya\Extension\Fee as FeeExtension;
use Lemuria\Model\Fantasya\Extension\Market as MarketExtension;
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
	private ?Construction $construction;

	protected function run(): void {
		$this->construction = $this->unit->Construction();
		if ($this->construction) {
			if ($this->unit !== $this->construction->Inhabitants()->Owner()) {
				$this->message(FeeNotOwnerMessage::class);
				return;
			}

			$building = $this->construction->Building();
			switch ($building::class) {
				case Market::class :
					$this->setMarketFee($building);
					break;
				case Port::class :
					$this->setPortFeeOrDuty($building);
					break;
				case Quay::class :
					$this->setQuayFee($building);
					break;
				default :
					$this->message(FeeNotApplicableMessage::class)->s($building);
			}
		} else {
			$this->message(FeeNotInsideMessage::class);
		}
	}

	private function setMarketFee(Market $building): void {
		/** @var MarketExtension $market */
		$market = $this->construction->Extensions()->offsetGet(MarketExtension::class);
		$fee    = $this->parseFee();
		$market->setFee($fee);
		if ($fee instanceof Quantity) {
			$this->message(FeeQuantityMessage::class, $this->construction)->s($building)->i($fee);
		} elseif (is_float($fee)) {
			$this->message(FeePercentMessage::class, $this->construction)->s($building)->p($fee);
		} else {
			$this->message(FeeNoneMessage::class, $this->construction)->s($building);
		}
	}

	private function setPortFeeOrDuty(Port $building): void {
		$fee = $this->parseFee();
		if ($fee instanceof Quantity) {
			/** @var FeeExtension $extension */
			$extension = $this->construction->Extensions()->offsetGet(Fee::class);
			$extension->setFee($fee);
			$this->message(VesselFeeQuantityMessage::class, $this->construction)->s($building)->i($fee);
		} elseif (is_float($fee)) {
			/** @var Duty $extension */
			$extension = $this->construction->Extensions()->offsetGet(Duty::class);
			$extension->setDuty($fee);
			$this->message(DutyMessage::class, $this->construction)->s($building)->p($fee);
		} else {
			/** @var FeeExtension $extension */
			$extension = $this->construction->Extensions()->offsetGet(Fee::class);
			$extension->setFee(null);
			$this->message(FeeNoneMessage::class, $this->construction)->s($building);
		}
	}

	private function setQuayFee(Quay $building): void {
		$fee = $this->parseFee();
		/** @var FeeExtension $extension */
		$extension = $this->construction->Extensions()->offsetGet(Fee::class);
		if ($fee instanceof Quantity) {
			$extension->setFee($fee);
			$this->message(VesselFeeQuantityMessage::class, $this->construction)->s($building)->i($fee);
		} else {
			$extension->setFee(null);
			$this->message(FeeNoneMessage::class, $this->construction)->s($building);
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
