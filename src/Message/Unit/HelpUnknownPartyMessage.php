<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class HelpUnknownPartyMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Id $party;

	protected function create(): string {
		return 'Unit ' . $this->id . ' does not know party ' . $this->party . ' to help.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = new Id($message->getParameter());
	}
}
