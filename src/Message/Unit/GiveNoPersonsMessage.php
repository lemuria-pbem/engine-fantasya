<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class GiveNoPersonsMessage extends GivePersonsToOwnMessage
{
	protected Id $recipient;

	protected function create(): string {
		return 'No persons given for transfer from unit ' . $this->id . ' to unit ' . $this->recipient . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->recipient = $message->get();
	}
}
