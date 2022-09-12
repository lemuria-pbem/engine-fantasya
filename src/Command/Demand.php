<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Message\Unit\DemandMessage;
use Lemuria\Model\Fantasya\Market\Trade;

/**
 * Create a demand trade for the market.
 *
 * - NACHFRAGE <amount> <commodity> <price> [<commodity>]
 * - NACHFRAGE <amount>-<amount> <commodity> <price> [<commodity>]
 * - NACHFRAGE <amount> <commodity> <price>-<price> [<commodity>]
 * - NACHFRAGE <amount>-<amount> <commodity> <price>-<price> [<commodity>]
 */
class Demand extends TradeCommand
{
	protected function run(): void {
		$trade = $this->createTrade()->setTrade(Trade::DEMAND);
		$this->unit->Trades()->add($trade);
		$this->message(DemandMessage::class)->e($trade);
	}
}
