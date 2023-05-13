<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Unit\PriceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TradeForbiddenPaymentMessage;
use Lemuria\Model\Fantasya\Commodity;

/**
 * Set new price for existing trade.
 *
 * - PREIS <trade> <amount>
 * - PREIS <trade> <amount> <commodity>
 * - PREIS <trade> <amount>-<amount>
 * - PREIS <trade> <amount>-<amount> <commodity>
 * - PREIS <trade> *
 * - PREIS <trade> * <commodity>
 */
final class Price extends AmountPriceCommand
{
	protected function run(): void {
		$trade = $this->parseTrade();
		if ($trade) {
			$deal = $this->parseDeal($trade->Price()->Commodity());
			$trade->setPrice($deal);
			$this->message(PriceMessage::class)->e($trade);
		}
	}

	protected function addForbiddenMessage(Commodity $commodity): void {
		$this->message(TradeForbiddenPaymentMessage::class)->s($commodity);
	}
}
