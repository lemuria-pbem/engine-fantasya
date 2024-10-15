<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Model\Fantasya\People;
use Lemuria\Model\Fantasya\Unit;

class Teacher implements \Countable
{
	private const int MAX_STUDENTS = 10;

	protected People $students;

	protected float $bonus = 0.0;

	protected float $fleetTime = 0.0;

	public function __construct(protected readonly Unit $unit) {
		$this->students = new People();
	}

	public function count(): int {
		return $this->students->Size();
	}

	public function hasStudent(Unit $unit): bool {
		return $this->students->has($unit->Id());
	}

	public function addStudent(Unit $unit): static {
		$this->students->add($unit);
		return $this;
	}

	public function setFleetTime(float $fleetTime): static {
		$this->fleetTime = $fleetTime;
		return $this;
	}

	public function calculateBonus(): float {
		if ($this->bonus <= 0.0) {
			$maxStudents = $this->unit->Size() * self::MAX_STUDENTS;
			$students    = $this->students->Size();
			$this->bonus = (1.0 - $this->fleetTime) * ($students > 0 ? min($maxStudents / $students, 1.0) : 1.0);
		}
		return $this->bonus;
	}
}
