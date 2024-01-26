<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class QuestUnknownMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected string $quest;

	protected function create(): string {
		return 'There is no quest with ID ' . $this->quest . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->quest = $message->getParameter();
	}
}
