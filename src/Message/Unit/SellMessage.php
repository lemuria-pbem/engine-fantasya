<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SellMessage extends BuyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' sold ' . $this->goods . ' to the peasants for ' . $this->payment . '.';
	}
}
