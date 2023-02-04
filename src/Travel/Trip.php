<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel;

class Trip
{
	private readonly array $talent;

	private readonly int $walkSpeed;

	public function __construct(private readonly int $walk, private readonly int $ride,
		                        private readonly Movement $movement, private readonly int $weight,
		                        readonly private int $speed, array|int $talent = 0, int $walkSpeed = 0) {
		if (is_array($talent)) {
			$this->talent = $talent;
		} else {
			$this->talent = [$talent, $talent];
		}
		if ($walkSpeed > 0) {
			$this->walkSpeed = $walkSpeed;
		} else {
			$this->walkSpeed = $this->speed;
		}
	}

	public function Movement(): Movement {
		return $this->movement;
	}

	public function Walk(): int {
		return $this->walk;
	}

	public function Ride(): int {
		return $this->ride;
	}

	public function Weight(): int {
		return $this->weight;
	}

	public function Speed(): int {
		return $this->speed;
	}

	public function Talent(): int {
		return $this->talent[0];
	}

	public function WalkingTalent(): int {
		return $this->talent[1];
	}

	public function WalkSpeed(): int {
		return $this->walkSpeed;
	}
}
