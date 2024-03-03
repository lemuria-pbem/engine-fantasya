<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SellOnlyMessage extends BuyOnlyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' can only sell ' . $this->goods . ' to the peasants in region ' . $this->region . ' for ' . $this->payment . '.';
	}
}
