<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class QuestNotAssignedMessage extends QuestNotHereMessage
{
	protected function create(): string {
		return 'We cannot start the quest ' . $this->quest . '.';
	}
}
