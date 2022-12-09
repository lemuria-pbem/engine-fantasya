<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class OperateNoUnicumMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected Section $section = Section::MAGIC;

	protected string $unicum;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no unicum with ID ' . $this->unicum . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->unicum = $message->getParameter();
	}
}
