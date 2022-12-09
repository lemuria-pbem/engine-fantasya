<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Party;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class DeceaseMessage extends AbstractPartyMessage
{
	protected Result $result = Result::Failure;

	protected Id $unit;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' dies.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unit = $message->get();
	}
}
