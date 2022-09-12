<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class RumorMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected Section $section = Section::MAIL;

	protected string $rumor;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will tell a rumor: ' . $this->rumor . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->rumor = $message->getParameter();
	}
}
