<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

class Wage
{
	protected const STEPS = [10 => 11, 50 => 12, 200 => 13, 750 => 14, 1500 => 15, 5000 => 16, 25000 => 17];

	public function __construct(protected readonly int $infrastructure) {
	}

	public function Infrastructure(): int {
		return $this->infrastructure;
	}

	/**
	 * @noinspection PhpUndefinedVariableInspection
	 */
	public function getWage(int $peasants = 1): int {
		foreach (self::STEPS as $infrastructure => $wage) {
			if ($this->infrastructure < $infrastructure) {
				return $peasants * $wage;
			}
		}
		return $peasants * ++$wage;
	}
}
