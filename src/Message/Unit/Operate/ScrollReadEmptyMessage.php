<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message;

class ScrollReadEmptyMessage extends ScrollWriteNothingMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'The ' . $this->composition . ' ' . $this->unicum . ' is empty.';
	}
}
