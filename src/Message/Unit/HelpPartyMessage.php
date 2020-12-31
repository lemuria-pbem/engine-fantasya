<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Id;

class HelpPartyMessage extends HelpMessage
{
	protected Id $party;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has set relation ' . $this->agreement . ' to party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
	}
}
