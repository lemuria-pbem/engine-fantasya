<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class LearnTeachersMessage extends AbstractUnitMessage
{
	protected int $teachers;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has ' . $this->teachers . ' teachers.';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->teachers = $message->getParameter();
	}

	protected function getTranslation(string $name): string {
		return $this->number($name, 'teachers') ?? parent::getTranslation($name);
	}
}
