<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Construction;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class NumberMessage extends AbstractConstructionMessage
{
	protected string $level = Message::SUCCESS;

	private Id $oldId;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'New ID of construction ' . $this->oldId . ' is ' . $this->id . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->oldId = new Id($message->getParameter());
	}
}
