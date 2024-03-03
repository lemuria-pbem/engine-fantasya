<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SellMessage extends BuyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' sells ' . $this->goods . ' to the peasants in region ' . $this->region . ' for ' . $this->payment . '.';
	}
}
