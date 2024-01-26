<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class QuestFinishedMessage extends QuestNotHereMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'We have finished the quest ' . $this->quest . ' and received our reward.';
	}
}
