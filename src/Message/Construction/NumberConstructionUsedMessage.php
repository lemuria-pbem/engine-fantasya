<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class NumberConstructionUsedMessage extends AbstractConstructionMessage
{
	protected string $level = Message::FAILURE;

	protected Id $newId;

	protected function create(): string {
		return 'ID of construction ' . $this->id . ' not changed. ID ' . $this->newId . ' is used already.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->newId = new Id($message->getParameter());
	}
}
