<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class QuestChoiceMessage extends QuestNotHereMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'We have finished the quest ' . $this->quest . ', now we can choose our reward.';
	}
}
