<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class NumberUnitMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Id $oldId;

	protected function create(): string {
		return 'New ID of unit ' . $this->oldId . ' is ' . $this->id . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->oldId = new Id($message->getParameter());
	}
}
