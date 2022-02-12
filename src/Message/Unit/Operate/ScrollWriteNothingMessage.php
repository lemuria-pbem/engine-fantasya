<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message;

class ScrollWriteNothingMessage extends AbstractOperateMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot write anything, the ' . $this->composition . ' ' . $this->unicum . ' is full.';
	}
}
