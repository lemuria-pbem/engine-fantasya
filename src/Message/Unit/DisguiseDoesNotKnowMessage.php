<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class DisguiseDoesNotKnowMessage extends DisguiseKnownPartyMessage
{
	protected Result $result = Result::DEBUG;

	protected function create(): string {
		return 'Unit ' . $this->id . ' does not know party ' . $this->party . '.';
	}
}
