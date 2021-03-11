<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Party\AbstractPartyMessage;
use Lemuria\Engine\Message;

class NamePartyMessage extends AbstractPartyMessage
{
	protected string $level = Message::SUCCESS;

	protected string $name;

	protected function create(): string {
		return 'Party ' . $this->id . ' is now known as ' . $this->name . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->name = $message->getParameter();
	}
}
