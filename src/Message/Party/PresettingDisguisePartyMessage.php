<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class PresettingDisguisePartyMessage extends PresettingDisguiseMessage
{
	protected Id $party;

	protected function create(): string {
		return 'New units will disguise as party ' . $this->party . ' by default.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
	}
}
