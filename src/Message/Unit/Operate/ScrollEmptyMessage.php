<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message\Result;

class ScrollEmptyMessage extends ScrollWriteNothingMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'The ' . $this->composition . ' ' . $this->id . ' contains no spell to learn.';
	}
}
