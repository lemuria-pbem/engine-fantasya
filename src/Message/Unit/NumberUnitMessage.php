<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class NumberUnitMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	private Id $oldId;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'New ID of unit ' . $this->oldId . ' is ' . $this->id . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->oldId = new Id($message->getParameter());
	}
}
