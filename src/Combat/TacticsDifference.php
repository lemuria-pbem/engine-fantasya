<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

class TacticsDifference
{
	private float $attacker;

	private float $defender;

	public function __construct(float $attacker, float $defender) {
		$this->attacker = $this->calculate($attacker, $defender);
		$this->defender = $this->calculate($defender, $attacker);
	}

	public function Attacker(): float {
		return $this->attacker;
	}

	public function Defender(): float {
		return $this->defender;
	}

	protected function calculate(float $first, float $second): float {
		$difference = $first - $second;
		return max(1.0, $difference);
	}
}
