<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class TeachBonusMessage extends AbstractUnitMessage
{
	public const STUDENTS = 's';

	public const BONUS = 'b';

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
