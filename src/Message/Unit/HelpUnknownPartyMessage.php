<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class HelpUnknownPartyMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Id $party;

	protected function create(): string {
		return 'Unit ' . $this->id . ' does not know party ' . $this->party . ' to help.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = new Id($message->getParameter());
	}
}
