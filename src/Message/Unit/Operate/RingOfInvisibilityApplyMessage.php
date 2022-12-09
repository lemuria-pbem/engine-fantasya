<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message\Result;

class RingOfInvisibilityApplyMessage extends AbstractOperateMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Unit ' . $this->id . ' puts on the ' . $this->composition . ' ' . $this->unicum . ' to become invisible.';
	}
}
