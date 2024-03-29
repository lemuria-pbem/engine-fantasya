<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class HelpPartyDeleteMessage extends HelpDeleteMessage
{
	protected Id $party;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has revoked all rights from party ' . $this->party . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
	}
}
