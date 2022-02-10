<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message;

class ScrollEmptyMessage extends ScrollWriteNothingMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'The ' . $this->composition . ' ' . $this->id . ' contains no spell to learn.';
	}
}
