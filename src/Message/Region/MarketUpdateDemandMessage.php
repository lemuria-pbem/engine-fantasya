<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

class MarketUpdateDemandMessage extends AbstractMarketUpdateMessage
{
	protected function direction(): string {
		return 'dropped';
	}
}
