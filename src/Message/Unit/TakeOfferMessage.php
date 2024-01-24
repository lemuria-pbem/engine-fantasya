<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class TakeOfferMessage extends TakeBoughtMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'Customer ' . $this->unit . ' buys unicum ' . $this->unicum . ' for ' . $this->payment . '.';
	}
}
