<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel;

final class StaminaBonus
{
	private const FACTOR = [0.0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.68, 0.76, 0.84, 0.9, 0.96, 1.0];

	private const N = 12;

	private const EXPONENT = 0.2777777778;

	public static function factor(int $level): float {
		return match (true) {
			$level <= 0       => 0.0,
			$level <= self::N => self::FACTOR[$level],
			default           => $level ** self::EXPONENT - 1.0
		};
	}
}
