<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class NameUnitMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected string $name;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' is now known as ' . $this->name . '.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->name = $message->getParameter();
	}
}
