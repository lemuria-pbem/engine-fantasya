<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class NumberRealmUsedMessage extends NumberUnitUsedMessage
{
	public final const string REALM = 'realm';

	protected string $realm;

	protected function create(): string {
		return 'ID of realm ' . $this->realm . ' not changed. ID ' . $this->newId . ' is used already.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->realm = $message->getParameter(self::REALM);
	}
}
