<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class BoardNotFoundMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Id $vessel;

	protected function create(): string {
		return 'Unit '. $this->id . ' cannot find the vessel ' . $this->vessel . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->vessel = new Id($message->getParameter());
	}
}
