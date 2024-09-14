<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Engine\Fantasya\Command\Learn;
use Lemuria\Engine\Fantasya\Command\Teach;
use Lemuria\Model\Fantasya\Unit;

class Studies
{
	/**
	 * @var array<Learn>
	 */
	private array $students = [];

	/**
	 * @var array<int, Teach>
	 */
	private array $teachers = [];

	private ?Teacher $teacher = null;

	public function __construct(protected readonly Unit $unit) {
	}

	/**
	 * Add student status for teaching.
	 */
	public function addStudent(Learn $student): static {
		$this->students[] = $student;
		return $this;
	}

	/**
	 * Add a teacher unit for learning.
	 */
	public function addTeacher(Teach $teacher): static {
		$id                  = $teacher->Unit()->Id()->Id();
		$this->teachers[$id] = $teacher;
		return $this;
	}

	public function getStudents(): array {
		return $this->students;
	}

	/**
	 * @return array<int, Teach>
	 */
	public function getTeachers(): array {
		return $this->teachers;
	}

	public function getTeacher(): Teacher {
		if (!$this->teacher) {
			$this->teacher = new Teacher($this->unit);
		}
		return $this->teacher;
	}
}
