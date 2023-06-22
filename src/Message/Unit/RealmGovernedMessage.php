<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class RealmGovernedMessage extends RealmAlreadyAddedMessage
{
	protected Id $party;

	protected function create(): string {
		return 'This region is governed by party ' . $this->party . ' and cannot be incorporated in our realm.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->party = $message->get();
	}
}
