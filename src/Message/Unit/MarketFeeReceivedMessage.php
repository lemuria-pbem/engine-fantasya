<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class MarketFeeReceivedMessage extends MarketFeePaidMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has received ' . $this->fee . ' as market fee from merchant ' . $this->unit . '.';
	}
}
