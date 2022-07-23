<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class GiveReceivedFromForeignMessage extends GiveReceivedMessage
{
	protected string $level = Message::EVENT;

	protected function create(): string {
		return 'Unit ' . $this->id . ' receives ' . $this->gift . ' from unit ' . $this->from . '.';
	}
}
