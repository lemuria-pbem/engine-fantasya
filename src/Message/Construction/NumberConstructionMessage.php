<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Construction;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class NumberConstructionMessage extends AbstractConstructionMessage
{
	protected Result $result = Result::Success;

	protected Id $oldId;

	protected function create(): string {
		return 'New ID of construction ' . $this->oldId . ' is ' . $this->id . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->oldId = new Id($message->getParameter());
	}
}
