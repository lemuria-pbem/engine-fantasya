<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class DisguiseUnknownPartyMessage extends DisguiseKnownPartyMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot claim belonging to unknown party ' . $this->party . '.';
	}
}
