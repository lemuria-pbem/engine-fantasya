<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class RealmAddMessage extends RealmAlreadyAddedMessage
{
	protected Result $result = Result::Success;

	protected string $realm;

	protected function create(): string {
		return 'Unit ' . $this->id . ' declares this region as part of our realm ' . $this->realm . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->realm = $message->getParameter();
	}
}
