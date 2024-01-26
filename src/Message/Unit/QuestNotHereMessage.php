<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class QuestNotHereMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Id $quest;

	protected function create(): string {
		return 'There is no quest with ID ' . $this->quest . ' here.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->quest = $message->get();
	}
}
