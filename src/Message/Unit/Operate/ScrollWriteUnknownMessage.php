<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message;

class ScrollWriteUnknownMessage extends ScrollWriteMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' does not know the spell ' . $this->spell . '.';
	}
}
