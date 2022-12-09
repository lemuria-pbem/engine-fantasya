<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class GivePersonsMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Id $recipient;

	protected int $persons;

	protected function create(): string {
		return 'Unit ' . $this->id . ' transfers ' . $this->persons . ' persons to unit ' . $this->recipient . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->recipient = $message->get();
		$this->persons   = $message->getParameter();
	}
}
