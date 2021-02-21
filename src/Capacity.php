<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use JetBrains\PhpStorm\Pure;

class Capacity
{
	public const WALK = 'walk';

	public const RIDE = 'ride';

	public const DRIVE = 'drive';

	public const SHIP = 'ship';

	public const FLY = 'fly';

	private array $talent;

	/**
	 * Capacity constructor.
	 *
	 * @param int $walk
	 * @param int $ride
	 * @param string $movement
	 * @param int $weight
	 * @param int $speed
	 * @param array|int $talent
	 */
	#[Pure] public function __construct(private int $walk, private int $ride, private string $movement,
		                                private int $weight, private int $speed, mixed $talent = 0) {
		if (is_array($talent)) {
			$this->talent = $talent;
		} else {
			$this->talent = [$talent, $talent];
		}
	}

	public function Movement(): string {
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
}
