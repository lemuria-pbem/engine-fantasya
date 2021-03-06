<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class DismissPeasantsMessage extends DismissEverybodyMessage
{
	protected int $persons;

	protected function create(): string {
		return 'Unit ' . $this->id . ' dismisses ' . $this->persons . ' persons to the peasants of region ' . $this->region . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->persons = $message->getParameter();
	}
}
