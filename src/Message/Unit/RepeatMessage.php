<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class RepeatMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Economy;

	protected Id $trade;

	protected function create(): string {
		return 'The trade ' . $this->trade . ' has been set to repeat.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->trade = $message->get();
	}
}
