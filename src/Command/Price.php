<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\PriceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\PriceUnicumMessage;
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
 *
 * - PREIS <Unicum> <price> [<commodity>]
 * - PREIS <Unicum> <price>-<price> [<commodity>]
 */
final class Price extends AmountPriceCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 3) {
			throw new InvalidCommandException($this);
		}
		$unicum = $this->createUnicumOffer();
		if ($unicum) {
			$this->message(PriceUnicumMessage::class)->e($unicum);
		} else {
			$trade = $this->parseTrade();
			if ($trade) {
				$deal = $this->parseDeal($trade->Price()->Commodity());
				$trade->setPrice($deal);
				$this->message(PriceMessage::class)->e($trade);
			}
		}
	}

	protected function addForbiddenMessage(Commodity $commodity): void {
		$this->message(TradeForbiddenPaymentMessage::class)->s($commodity);
	}
}
