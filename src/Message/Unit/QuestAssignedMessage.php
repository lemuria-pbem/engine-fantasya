<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class QuestAssignedMessage extends QuestFinishedMessage
{
	protected function create(): string {
		return 'We have started the quest ' . $this->quest . '.';
	}
}
