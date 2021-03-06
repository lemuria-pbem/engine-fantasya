<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class ContactNotFoundMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find unit ' . $this->unit . ' to contact.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = new Id($message->getParameter());
	}
}
