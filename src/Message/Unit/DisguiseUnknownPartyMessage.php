<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class DisguiseUnknownPartyMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected string $party;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot claim belonging to unknown party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->getParameter();
	}
}
