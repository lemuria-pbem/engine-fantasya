<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message\Result;

class CarcassNothingMessage extends AbstractOperateMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find anything in ' . $this->composition . ' ' . $this->unicum . '.';
	}
}
