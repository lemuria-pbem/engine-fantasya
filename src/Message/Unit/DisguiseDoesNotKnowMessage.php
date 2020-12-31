<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class DisguiseDoesNotKnowMessage extends DisguiseKnownPartyMessage
{
	protected string $level = Message::DEBUG;

	protected function create(): string {
		return 'Unit ' . $this->id . ' does not know party ' . $this->party . '.';
	}
}
