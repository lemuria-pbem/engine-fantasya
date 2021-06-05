<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class TeachStudentMessage extends AbstractUnitMessage
{
	protected string $level = Message::SUCCESS;

	protected int $section = Section::STUDY;

	protected Id $student;

	protected function create(): string {
		return 'Unit ' . $this->id . ' teaches unit ' . $this->student . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->student = $message->get();
	}
}
