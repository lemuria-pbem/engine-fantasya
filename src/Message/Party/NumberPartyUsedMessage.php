<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class NumberPartyUsedMessage extends AbstractPartyMessage
{
	protected string $level = Message::FAILURE;

	protected Id $newId;

	protected function create(): string {
		return 'ID of party ' . $this->id . ' not changed. ID ' . $this->newId . ' is used already.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->newId = new Id($message->getParameter());
	}
}
