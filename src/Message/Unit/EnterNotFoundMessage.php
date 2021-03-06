<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class EnterNotFoundMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Id $construction;

	protected function create(): string {
		return 'Unit '. $this->id . ' cannot find the construction ' . $this->construction . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->construction = new Id($message->getParameter());
	}
}
