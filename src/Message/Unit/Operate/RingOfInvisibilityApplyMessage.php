<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message;

class RingOfInvisibilityApplyMessage extends AbstractOperateMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' puts on the ' . $this->composition . ' ' . $this->unicum . ' to become invisible.';
	}
}
