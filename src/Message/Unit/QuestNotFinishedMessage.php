<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class QuestNotFinishedMessage extends QuestNotHereMessage
{
	protected function create(): string {
		return 'We have not finished our quest ' . $this->quest . ' yet.';
	}
}
