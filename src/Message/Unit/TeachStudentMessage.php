<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;
use Lemuria\Id;

class TeachStudentMessage extends AbstractUnitMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::STUDY;

	protected Id $student;

	protected function create(): string {
		return 'Unit ' . $this->id . ' teaches unit ' . $this->student . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->student = $message->get();
	}
}
