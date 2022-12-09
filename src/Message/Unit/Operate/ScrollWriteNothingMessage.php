<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message\Result;

class ScrollWriteNothingMessage extends AbstractOperateMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot write anything, the ' . $this->composition . ' ' . $this->unicum . ' is full.';
	}
}
