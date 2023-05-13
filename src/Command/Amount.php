<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Unit\AmountMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TradeForbiddenCommodityMessage;
use Lemuria\Model\Fantasya\Commodity;

/**
 * Set new amount for existing trade.
 *
 * - MENGE <trade> <amount>
 * - MENGE <trade> <amount> <commodity>
 * - MENGE <trade> <amount>-<amount>
 * - MENGE <trade> <amount>-<amount> <commodity>
 * - MENGE <trade> *
 * - MENGE <trade> * <commodity>
 */
final class Amount extends AmountPriceCommand
{
	protected function run(): void {
		$trade = $this->parseTrade();
		if ($trade) {
			$deal = $this->parseDeal($trade->Goods()->Commodity());
			$trade->setGoods($deal);
			$this->message(AmountMessage::class)->e($trade);
		}
	}

	protected function addForbiddenMessage(Commodity $commodity): void {
		$this->message(TradeForbiddenCommodityMessage::class)->s($commodity);
	}
}
