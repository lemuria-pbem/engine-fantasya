<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class NumberVesselUsedMessage extends AbstractVesselMessage
{
	protected string $level = Message::FAILURE;

	protected Id $newId;

	protected function create(): string {
		return 'ID of vessel ' . $this->id . ' not changed. ID ' . $this->newId . ' is used already.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->newId = new Id($message->getParameter());
	}
}
