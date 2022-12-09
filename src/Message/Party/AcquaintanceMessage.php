<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class AcquaintanceMessage extends AbstractPartyMessage
{
	protected Result $result = Result::SUCCESS;

	protected string $party;

	protected function create(): string {
		return 'We have encountered a new party: ' . $this->party;
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->getParameter();
	}
}
