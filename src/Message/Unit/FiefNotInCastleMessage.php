<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Id;

class FiefNotInCastleMessage extends FiefNoneMessage
{
	protected Id $receiver;

	protected function create(): string {
		return 'Unit ' . $this->receiver . ' must be in the castle for handing over the realm.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->receiver = $message->get();
	}
}
