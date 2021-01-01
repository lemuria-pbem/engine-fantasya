<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class BoardNotFoundMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Id $vessel;

	protected function create(): string {
		return 'Unit '. $this->id . ' cannot find the vessel ' . $this->vessel . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->vessel = new Id($message->getParameter());
	}
}
