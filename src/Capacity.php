<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;

class Capacity
{
	public const WALK = 'walk';

	public const RIDE = 'ride';

	public const DRIVE = 'drive';

	public const SHIP = 'ship';

	public const FLY = 'fly';

	private readonly array $talent;

	private readonly int $walkSpeed;

	#[Pure] public static function forVessel(Vessel $vessel): self {
		$ship   = $vessel->Ship();
		$weight = 0;
		foreach ($vessel->Passengers() as $unit /* @var Unit $unit */) {
			$weight += $unit->Weight();
		}
		$payload = (int)floor($vessel->Completion() * $ship->Payload());
		return new Capacity(0, $payload, Capacity::SHIP, $weight, $ship->Speed(), $ship->Crew());
	}

	/**
	 * Capacity constructor.
	 *
	 * @param int $walk
	 * @param int $ride
	 * @param string $movement
	 * @param int $weight
	 * @param int $speed
	 * @param array|int $talent
	 * @param int $walkSpeed
	 */
	#[Pure] public function __construct(private readonly int $walk, private readonly int $ride,
		                                private readonly string $movement,
		                                private readonly int $weight, readonly private int $speed,
		                                mixed $talent = 0, int $walkSpeed = 0) {
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

	public function WalkSpeed(): int {
		return $this->walkSpeed;
	}
}
