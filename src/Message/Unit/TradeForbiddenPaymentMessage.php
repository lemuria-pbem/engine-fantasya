<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TradeForbiddenPaymentMessage extends TradeForbiddenCommodityMessage
{
	protected function create(): string {
		return 'It is not allowed to pay with ' . $this->commodity . ' on this market.';
	}
}
