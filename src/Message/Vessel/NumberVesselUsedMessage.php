<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class NumberVesselUsedMessage extends AbstractVesselMessage
{
	protected Result $result = Result::FAILURE;

	protected Id $newId;

	protected function create(): string {
		return 'ID of vessel ' . $this->id . ' not changed. ID ' . $this->newId . ' is used already.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->newId = new Id($message->getParameter());
	}
}
