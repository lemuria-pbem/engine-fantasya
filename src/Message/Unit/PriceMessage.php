<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class PriceMessage extends AmountMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' sets a new payment price for trade ' . $this->trade . '.';
	}
}
