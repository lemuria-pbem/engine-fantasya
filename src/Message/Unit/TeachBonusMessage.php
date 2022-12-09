<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Message\Section;

class TeachBonusMessage extends AbstractUnitMessage
{
	public final const STUDENTS = 's';

	public final const BONUS = 'b';

	protected Section $section = Section::Study;

	protected int $students;

	protected float $bonus;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has ' . $this->students . ' students (bonus: ' . $this->bonus . ').';
	}

	protected function getData(LemuriaMessage $message): void {
		parent::getData($message);
		$this->students = $message->getParameter(self::STUDENTS);
		$this->bonus = $message->getParameter(self::BONUS);
	}

	protected function getTranslation(string $name): string {
		$students = $this->number($name, 'students');
		if ($students) {
			return $students;
		}
		$bonus = $this->number($name, 'bonus');
		if ($bonus) {
			return $bonus;
		}
		return parent::getTranslation($name);
	}
}
