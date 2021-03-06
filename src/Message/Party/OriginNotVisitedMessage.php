<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class OriginNotVisitedMessage extends AbstractPartyMessage
{
	protected string $level = Message::FAILURE;

	protected Id $region;

	protected function create(): string {
		return 'Map origin cannot be set to region ' . $this->region . ', we have not visited it yet.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->region = $message->get();
	}
}
