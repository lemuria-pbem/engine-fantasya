<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Message;
use Lemuria\Id;

class TeachStudentMessage extends AbstractUnitMessage
{
	public const STUDENT = 'student';

	protected string $level = Message::SUCCESS;

	protected Id $student;

	protected function create(): string {
		return 'Unit ' . $this->id . ' teaches unit ' . $this->student . '.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->student = $message->get(self::STUDENT);
	}
}
