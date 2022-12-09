<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Id;

class NumberNoUnicumMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Id $unicum;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no unicum ' . $this->unicum . ' that can be renumbered.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unicum = new Id($message->getParameter());
	}
}
