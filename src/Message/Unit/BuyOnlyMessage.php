<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class BuyOnlyMessage extends BuyMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only buy ' . $this->goods . ' from the peasants in region ' . $this->region . ' for ' . $this->payment . '.';
	}
}
