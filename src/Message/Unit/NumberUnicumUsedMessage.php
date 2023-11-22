<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class NumberUnicumUsedMessage extends AbstractUnitMessage
{
	public final const string NEW_ID = 'newId';

	protected Result $result = Result::Failure;

	protected Id $oldId;

	protected Id $newId;

	protected function create(): string {
		return 'ID of unicum ' . $this->oldId . ' not changed. ID ' . $this->newId . ' is used already.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->oldId = new Id($message->getParameter());
		$this->newId = new Id($message->getParameter(self::NEW_ID));
	}
}
