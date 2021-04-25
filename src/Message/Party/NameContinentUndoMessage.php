<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;

class NameContinentUndoMessage extends AbstractPartyMessage
{
	protected string $level = Message::SUCCESS;

	protected string $continent;

	protected function create(): string {
		return 'Party ' . $this->id . ' has discarded its name for the continent ' . $this->continent . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->continent = $message->getParameter();
	}
}
