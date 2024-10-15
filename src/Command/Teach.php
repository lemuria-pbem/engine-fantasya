<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Exception\ActivityException;
use Lemuria\Engine\Fantasya\Exception\AlternativeException;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Teacher;
use Lemuria\Engine\Fantasya\Factory\ModifiedActivityTrait;
use Lemuria\Engine\Fantasya\Factory\RealmTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Unit\TeachBonusMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeachEmptyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeachExceptionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeachFleetMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeachFleetNothingMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeachForeignMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeachPartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeachRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeachSelfMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeachSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeachStudentMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TeachUnableMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Id;
use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

/**
 * Implementation of command LEHREN (teach other units).
 *
 * The command helps target units to learn.
 *
 * - LEHREN
 * - LEHREN <Unit>...
 */
final class Teach extends UnitCommand implements Activity, Reassignment
{
	use CamouflageTrait;
	use ModifiedActivityTrait;
	use RealmTrait;
	use ReassignTrait;
	use SiegeTrait;

	/**
	 * @var array<int, Learn>
	 */
	private array $students = [];

	private int $size = 0;

	private float $bonus = 0.0;

	private float $fleetTime = 0.0;

	private Teacher $teacher;

	private bool $logCommit = false;

	private bool $abortTeaching = false;

	public function reassign(Id $oldId, Identifiable $identifiable): void {
		if ($this->checkReassignmentDomain($identifiable->Catalog())) {
			$old       = (string)$oldId;
			$new       = (string)$identifiable->Id();
			$oldPhrase = $this->getReassignPhrase($old, $new);
			if ($oldPhrase) {
				$phrase       = str_replace($old, $new, $oldPhrase);
				$this->phrase = new Phrase($phrase);
				$this->context->getProtocol($this->unit)->reassignDefaultActivity($oldPhrase, $this);
			}
		}
	}

	/**
	 * Get learning bonus.
	 */
	public function getBonus(): float {
		return $this->bonus;
	}

	public function hasTaught(Learn $student): void {
		$unit = $student->Unit();
		if ($this->unit->Party() !== $unit->Party()) {
			$talent = $student->getTalent();
			$level  = $this->context->getCalculus($unit)->ability($talent)->Level();
			$this->message(TeachForeignMessage::class)->e($unit)->s($talent)->p($level);
		}
		if (!$this->canTeach($student) && $student->getLevel() <= 0) {
			$id = $unit->Id()->Id();
			unset($this->students[$id]);
			$ids = [];
			foreach ($this->students as $student) {
				$ids[] = $student->Unit()->Id();
			}
			$this->createNewDefault($ids);
			$this->context->getProtocol($this->unit)->replaceDefaults($this);
			Lemuria::Catalog()->addReassignment($this);
		}
	}

	protected function initialize(): void {
		parent::initialize();
		if (!$this->checkSize() && $this->IsDefault()) {
			Lemuria::Log()->debug('Teach command skipped due to empty unit.', ['command' => $this]);
			return;
		}

		$this->teacher = $this->calculus()->getTeacher();
		if ($this->isRunCentrally($this)) {
			$realm           = $this->unit->Region()->Realm();
			$this->fleetTime = $this->context->getRealmFleet($realm)->getUsedCapacity($this->unit);
			$this->teacher->setFleetTime($this->fleetTime);
		}

		$ids = [];
		$i   = 1;
		$n   = count($this->phrase);
		if ($n >= $i) {
			// Add specific students.
			while ($i <= $n) {
				try {
					$unit = $this->nextId($i);
					if ($unit) {
						if ($this->checkUnit($unit)) {
							$this->size += $this->teach($unit, true);
							$ids[]       = $unit->Id();
						}
					}
				} catch (CommandException $e) {
					$this->message(TeachExceptionMessage::class)->p($e->getTranslation());
				}
			}
		} else {
			// Add all units in region as possible students.
			foreach ($this->unit->Region()->Residents() as $unit) {
				if ($this->checkUnit($unit)) {
					$this->size += $this->teach($unit);
					$ids[]       = $unit->Id();
				}
			}
		}
		if (empty($ids)) {
			$this->abortTeaching = true;
			Lemuria::Log()->debug('Teach command execution skipped due to missing students.', ['command' => $this]);
		} else {
			$this->createNewDefault($ids);
			$this->logCommit = true;
			$this->commitCommand($this);
		}
	}

	protected function run(): void {
		if ($this->fleetTime > 0.0) {
			if ($this->fleetTime < 1.0) {
				$this->message(TeachFleetMessage::class);
			} else {
				$this->message(TeachFleetNothingMessage::class);
			}
		}
		$this->bonus = $this->teacher->calculateBonus();
		if ($this->fleetTime < 1.0) {
			$bonus = round($this->bonus ** 2, 3);
			$this->message(TeachBonusMessage::class)->p($this->size, TeachBonusMessage::STUDENTS)->p($bonus, TeachBonusMessage::BONUS);
		}
	}

	protected function commitCommand(UnitCommand $command): void {
		$protocol = $this->context->getProtocol($this->unit);
		if ($protocol->hasActivity($this)) {
			if ($this->isAlternative()) {
				$protocol->logCurrent($command);
				throw new AlternativeException($command);
			}
			throw new ActivityException($command);
		}
		if ($this->size > 0) {
			parent::commitCommand($command);
		} elseif ($this->logCommit) {
			$protocol->logCurrent($command);
		}
	}

	protected function checkSize(): bool {
		return parent::checkSize() && !$this->abortTeaching;
	}

	private function checkUnit(Unit $unit): bool {
		if ($unit->Size() <= 0) {
			$this->message(TeachEmptyMessage::class)->e($unit);
			return false;
		}
		if ($unit->Region() !== $this->unit->Region()) {
			if (!$this->isAlternative()) {
				$this->message(TeachRegionMessage::class)->e($unit);
			}
			return false;
		}
		if ($this->isStoppedBySiege($this->unit, $unit)) {
			$this->message(TeachSiegeMessage::class)->e($unit);
			return false;
		}
		if ($unit->Party() === $this->unit->Party()) {
			return !$this->teacher->hasStudent($unit);
		}
		if (!$this->checkVisibility($this->unit, $unit)) {
			if (!$this->isAlternative()) {
				$this->message(TeachRegionMessage::class)->e($unit);
			}
			return false;
		}
		return !$this->teacher->hasStudent($unit);
	}

	/**
	 * Add student/teacher for given unit.
	 */
	private function teach(Unit $unit, bool $log = false): int {
		$size = 0;
		if ($unit->Id()->Id() !== $this->unit->Id()->Id()) {
			if ($unit->Region()->Id()->Id() === $this->unit->Region()->Id()->Id()) {
				if ($this->isAllowedToTeach($unit)) {
					$check = $this->hasGreaterLevelThan($unit);
					if ($check === null) {
						return $size;
					}
					if ($check) {
						$this->message(TeachStudentMessage::class)->e($unit);
						$size = $unit->Size();
					} elseif ($log) {
						if (!$this->isAlternative()) {
							$this->message(TeachUnableMessage::class)->e($unit);
						}
					}
				} elseif ($log) {
					$this->message(TeachPartyMessage::class)->e($unit, TeachPartyMessage::UNIT)->e($unit->Party());
				}
			} else {
				if (!$this->isAlternative()) {
					$this->message(TeachRegionMessage::class)->e($unit);
				}
			}
		} elseif ($log) {
			$this->message(TeachSelfMessage::class);
		}
		return $size;
	}

	private function isAllowedToTeach(Unit $student): bool {
		$party = $this->unit->Party();
		// Teacher and student of the same party is always allowed.
		if ($student->Party()->Id()->Id() === $party->Id()->Id()) {
			return true;
		}
		// If teacher is NPC, there is a teaching quest active.
		if ($party->Type() === Type::NPC) {
			return true;
		}
		return false;
	}

	/**
	 * Check if teacher has more experience than student candidate.
	 */
	private function hasGreaterLevelThan(Unit $unit): ?bool {
		if ($this->context->getTurnOptions()->IsSimulation() && $unit->Party() !== $this->unit->Party()) {
			return null;
		}

		$calculus = $this->context->getCalculus($unit);
		foreach ($calculus->getStudents() as $learn) {
			if ($learn->canLearn()) {
				if ($this->canTeach($learn)) {
					$calculus->addTeacher($this);
					$this->students[$unit->Id()->Id()] = $learn;
					$this->teacher->addStudent($learn->Unit());
					return true;
				}
				return false;
			}
		}
		return null;
	}

	private function canTeach(Learn $student): bool {
		$talent         = $student->getTalent();
		$studentAbility = $this->context->getCalculus($student->unit)->ability($talent);
		$teacherAbility = $this->calculus()->ability($talent);
		$level          = $studentAbility->Level();
		$levelToReach   = $student->getLevel();
		if ($levelToReach > 0 && $level >= $levelToReach) {
			return false;
		}
		return $teacherAbility->Level() > $level;
	}

	private function createNewDefault(array $ids): void {
		if (empty($ids)) {
			$this->newDefault = null;
		} else {
			$teach = $this->phrase->getVerb() . ' ' . implode(' ', $ids);
			/** @var Teach $command */
			$command = $this->context->Factory()->create(new Phrase($teach));
			if ($this->isAlternative()) {
				$command->setAlternative();
			}
			$this->newDefault = $command;
		}
	}
}
