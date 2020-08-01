<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class LearnTeachersMessage extends AbstractUnitMessage
{
	private int $teachers;

	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' has ' . $this->teachers . ' teachers.';
	}

	/**
	 * @param LemuriaMessage $message
	 */
	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->teachers = $message->getParameter();
	}
}
