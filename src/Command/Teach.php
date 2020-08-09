<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use function Lemuria\getClass;
use Lemuria\Engine\Lemuria\Message\Unit\TeachPartyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TeachRegionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TeachSelfMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TeachUnableMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TeachExceptionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\TeachStudentMessage;
use Lemuria\Engine\Lemuria\Activity;
use Lemuria\Engine\Lemuria\Exception\CommandException;
use Lemuria\Engine\Lemuria\Message\Unit\TeachBonusMessage;
use Lemuria\Model\Lemuria\Unit;

/**
 * Implementation of command LEHREN (teach other units).
 *
 * The command helps target units to learn.
 *
 * - LEHREN
 * - LEHREN <Unit>...
 */
final class Teach extends UnitCommand implements Activity
{
	private const MAX_STUDENTS = 10;

	/**
	 * @var array(int=>Learn)
	 */
	private array $students = [];

	private int $size = 0;

	private float $bonus = 0.0;

	/**
	 * Get learning bonus.
	 *
	 * @return float
	 */
	public function getBonus(): float {
		return $this->bonus;
	}

	/**
	 * Make preparations before running the command.
	 */
	protected function initialize(): void {
		parent::initialize();
		$i = 1;
		if (count($this->phrase) >= $i) {
			// Add specific students.
			while (true) {
				try {
					$unit = $this->nextId($i);
					if ($unit) {
						$this->size += $this->teach($unit, true);
					} else {
						break;
					}
				} catch (CommandException $e) {
					$this->message(TeachExceptionMessage::class)->p($e->getMessage());
				}
			}
		} else {
			// Add all units in region as possible students.
			foreach ($this->unit->Region()->Residents() as $unit /* @var Unit $unit */) {
				$this->size += $this->teach($unit);
			}
		}
	}

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		$this->calculateBonuses();
		$this->message(TeachBonusMessage::class)->p($this->size, TeachBonusMessage::STUDENTS)->p($this->bonus, TeachBonusMessage::BONUS);
	}

	/**
	 * Add student/teacher for given unit.
	 *
	 * @param Unit $unit
	 * @param bool $log
	 * @return int
	 */
	private function teach(Unit $unit, bool $log = false): int {
		$size = 0;
		if ($unit->Id()->Id() !== $this->unit->Id()->Id()) {
			if ($unit->Region()->Id()->Id() === $this->unit->Region()->Id()->Id()) {
				if ($unit->Party()->Id()->Id() === $this->unit->Party()->Id()->Id()) {
					$check = $this->hasGreaterLevelThan($unit);
					if ($check === null) {
						return $size;
					}
					if ($check) {
						$this->message(TeachStudentMessage::class)->e($unit, TeachStudentMessage::STUDENT);
						$size = $unit->Size();
					} elseif ($log) {
						$this->message(TeachUnableMessage::class)->e($unit, TeachUnableMessage::STUDENT);
					}
				} elseif ($log) {
					$this->message(TeachPartyMessage::class)->e($unit, TeachPartyMessage::UNIT)->e($unit->Party());
				}
			} else {
				$this->message(TeachRegionMessage::class)->e($unit, TeachRegionMessage::STUDENT);
			}
		} elseif ($log) {
			$this->message(TeachSelfMessage::class);
		}
		return $size;
	}

	/**
	 * Check if teacher has more experience than student candidate.
	 *
	 * @param Unit $unit
	 * @return bool|null
	 */
	private function hasGreaterLevelThan(Unit $unit) {
		$calculus = $this->context->getCalculus($unit);
		$learn    = $calculus->getStudent();
		if ($learn) {
			$talent         = getClass($learn->getTalent());
			$studentAbility = $calculus->knowledge($talent);
			$teacherAbility = $this->calculus()->knowledge($talent);
			if ($teacherAbility->Level() > $studentAbility->Level()) {
				$calculus->addTeacher($this);
				$this->students[$unit->Id()->Id()] = $learn;
				return true;
			}
			return false;
		} else {
			return null;
		}
	}

	/**
	 * Calculate the bonus for every student.
	 */
	private function calculateBonuses(): void {
		$people = 0;
		foreach ($this->students as $id => $learn /* @var Learn $learn */) {
			$people += $learn->Unit()->Size();
		}
		$this->bonus = $people > 0 ? min(self::MAX_STUDENTS / $people, 1.0) ** 2 : 1.0;
	}
}
