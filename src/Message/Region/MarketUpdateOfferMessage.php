<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

class MarketUpdateOfferMessage extends AbstractMarketUpdateMessage
{
	protected function direction(): string {
		return 'raised';
	}
}
