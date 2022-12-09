<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class GiveReceivedFromForeignMessage extends GiveReceivedMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'Unit ' . $this->id . ' receives ' . $this->gift . ' from unit ' . $this->from . '.';
	}
}
