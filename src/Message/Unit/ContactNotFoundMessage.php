<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class ContactNotFoundMessage extends ContactMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find unit ' . $this->unit . ' to contact.';
	}

	protected function getData(LemuriaMessage $message): void {
		$this->unit = new Id($message->getParameter());
	}
}
