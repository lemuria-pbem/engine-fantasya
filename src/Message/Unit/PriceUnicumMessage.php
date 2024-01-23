<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class PriceUnicumMessage extends AmountMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' sets a new payment price for unicum ' . $this->trade . '.';
	}
}
