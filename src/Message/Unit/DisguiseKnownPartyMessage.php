<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class DisguiseKnownPartyMessage extends AbstractUnitMessage
{
	protected Result $result = Result::SUCCESS;

	protected Id $party;

	protected function create(): string {
		return 'Unit ' . $this->id . ' claims belonging to party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
	}
}
