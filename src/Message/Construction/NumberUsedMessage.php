<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Construction;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class NumberUsedMessage extends AbstractConstructionMessage
{
	protected string $level = Message::FAILURE;

	private Id $newId;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'ID of construction ' . $this->id . ' not changed. ID ' . $this->newId . ' is used already.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->newId = new Id($message->getParameter());
	}
}
