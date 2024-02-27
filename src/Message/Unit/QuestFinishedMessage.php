<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class QuestFinishedMessage extends QuestCompletedMessage
{
	protected function create(): string {
		return 'We have finished the quest ' . $this->quest . ' and received our reward.';
	}
}
