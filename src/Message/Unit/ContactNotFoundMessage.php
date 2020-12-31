<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class ContactNotFoundMessage extends UnitMessage
{
	protected string $level = Message::FAILURE;

	protected string $contact;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find unit ' . $this->contact . ' to contact.';
	}

	protected function getData(LemuriaMessage $message): void {
		$this->contact = $message->getParameter();
	}
}
