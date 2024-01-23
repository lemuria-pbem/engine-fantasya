<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\OfferMessage;
use Lemuria\Engine\Fantasya\Message\Unit\OfferUnicumMessage;
use Lemuria\Model\Fantasya\Market\Trade;

/**
 * Create an offer trade for the market.
 *
 * - ANGEBOT <amount> <commodity> <price> [<commodity>]
 * - ANGEBOT <amount>-<amount> <commodity> <price> [<commodity>]
 * - ANGEBOT * <commodity> <price> [<commodity>]
 * - ANGEBOT <amount> <commodity> <price>-<price> [<commodity>]
 * - ANGEBOT <amount>-<amount> <commodity> <price>-<price> [<commodity>]
 *
 * - ANGEBOT <Unicum> <price> [<commodity>]
 * - ANGEBOT <Unicum> <price>-<price> [<commodity>]
 */
class Offer extends TradeCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 3) {
			throw new InvalidCommandException($this);
		}
		$unicum = $this->createUnicumOffer();
		if ($unicum) {
			$this->message(OfferUnicumMessage::class)->e($unicum);
		} else {
			$trade = $this->createTrade()->setTrade(Trade::OFFER);
			$this->unit->Trades()->add($trade);
			$this->message(OfferMessage::class)->e($trade);
		}
	}
}
