<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class AttackAlliedPartyMessage extends AttackOwnPartyMessage
{
	protected string $party;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot attack allied party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->getParameter();
	}
}
