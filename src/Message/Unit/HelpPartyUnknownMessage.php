<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class HelpPartyUnknownMessage extends HelpPartyMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot set a relation to unknown party ' . $this->party . '.';
	}
}
