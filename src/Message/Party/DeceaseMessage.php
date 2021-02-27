<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Party;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class DeceaseMessage extends AbstractPartyMessage
{
	protected string $level = Message::FAILURE;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' dies.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
