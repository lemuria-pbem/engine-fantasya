<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class TakeDemandMessage extends TakeBoughtMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'Customer ' . $this->unit . ' sold unicum ' . $this->unicum . ' to us for ' . $this->payment . '.';
	}
}
