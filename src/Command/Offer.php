<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Unit\OfferMessage;
use Lemuria\Model\Fantasya\Market\Trade;

/**
 * Create an offer trade for the market.
 *
 * - ANGEBOT <amount> <commodity> <price> [<commodity>]
 * - ANGEBOT <amount>-<amount> <commodity> <price> [<commodity>]
 * - ANGEBOT <amount> <commodity> <price>-<price> [<commodity>]
 * - ANGEBOT <amount>-<amount> <commodity> <price>-<price> [<commodity>]
 */
class Offer extends TradeCommand
{
	protected function run(): void {
		$trade = $this->createTrade()->setTrade(Trade::OFFER);
		$this->unit->Trades()->add($trade);
		$this->message(OfferMessage::class)->e($trade);
	}
}
