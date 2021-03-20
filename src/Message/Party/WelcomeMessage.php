<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class WelcomeMessage extends AbstractPartyMessage
{
	protected string $level = Message::SUCCESS;

	protected string $party;

	protected function create(): string {
		return 'Welcome to Lemuria! Today you start the game with the party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->getParameter();
	}
}
