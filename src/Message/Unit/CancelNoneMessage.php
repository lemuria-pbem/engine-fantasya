<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class CancelNoneMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Economy;

	protected string $trade;

	protected function create(): string {
		return 'There is no trade ' . $this->trade . ' to cancel.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->trade = $message->getParameter();
	}
}
