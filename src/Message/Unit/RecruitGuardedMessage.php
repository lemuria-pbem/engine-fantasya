<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message;

use Lemuria\Engine\Lemuria\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message;

class RecruitGuardedMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot recruit, the region is guarded.';
	}
}
