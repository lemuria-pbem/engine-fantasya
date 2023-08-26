<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Model\Fantasya\Unit;

class Teacher
{
	private const MAX_STUDENTS = 10;

	protected int $students = 0;

	protected float $bonus = 0.0;

	protected float $fleetTime = 0.0;

	public function __construct(protected readonly Unit $unit) {
	}

	public function addStudents(int $students): static {
		$this->students += $students;
		return $this;
	}

	public function setFleetTime(float $fleetTime): static {
		$this->fleetTime = $fleetTime;
		return $this;
	}

	public function calculateBonus(): float {
		if ($this->bonus <= 0.0) {
			$maxStudents = $this->unit->Size() * self::MAX_STUDENTS;
			$this->bonus = (1.0 - $this->fleetTime) * ($this->students > 0 ? min($maxStudents / $this->students, 1.0) : 1.0);
		}
		return $this->bonus;
	}
}
