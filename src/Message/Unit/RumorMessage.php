<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class RumorMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Mail;

	protected string $rumor;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will tell a rumor: ' . $this->rumor . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->rumor = $message->getParameter();
	}
}
