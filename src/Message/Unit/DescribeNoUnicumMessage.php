<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;

class DescribeNoUnicumMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected string $unicum;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no unicum with ID ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unicum = $message->getParameter();
	}
}
