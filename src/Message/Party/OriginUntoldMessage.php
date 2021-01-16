<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class OriginUntoldMessage extends AbstractPartyMessage
{
	protected string $level = Message::FAILURE;

	protected Id $party;

	protected function create(): string {
		return 'Map origin cannot be set to party ' . $this->party . ', we do not know where it is.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
	}
}
