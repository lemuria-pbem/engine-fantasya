<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class BuyOnlyMessage extends BuyMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only buy ' . $this->goods . ' from the peasants for ' . $this->payment . '.';
	}
}
