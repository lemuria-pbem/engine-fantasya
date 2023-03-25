<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Talent\Tactics;
use Lemuria\Model\Fantasya\Unit;

class TacticsData implements \Countable
{
	protected array $sizes = [];

	protected array $tactics = [];

	protected array $isAttacker = [];

	public function count(): int {
		return count($this->sizes);
	}

	/**
	 * @param array<Unit> $units
	 */
	public function add(array $units, bool $isAttacker): TacticsData {
		foreach ($units as $unit) {
			$party = $unit->Party()->Id()->Id();
			if (!isset($this->sizes[$party])) {
				$this->sizes[$party]      = 0;
				$this->tactics[$party]    = 0;
				$this->isAttacker[$party] = $isAttacker;
			}
			$calculus               = new Calculus($unit);
			$level                  = $calculus->knowledge(Tactics::class)->Level();
			$size                   = $unit->Size();
			$this->sizes[$party]   += $size;
			$this->tactics[$party] += $size * $level ** 3;
		}
		return $this;
	}

	/**
	 * @return array<Id>
	 */
	public function getBestTacticsCandidates(): array {
		//TODO Calculate average tactics value.
		arsort($this->tactics);
		$party      = array_keys($this->tactics);
		$talent     = array_values($this->tactics);

		$n          = $this->count();
		$candidates = [0];
		for ($i = 1; $i < $n; $i++) {
			if ($talent[$i] < $talent[0]) {
				break;
			}
			$candidates[] = $i;
		}

		$n = count($candidates);
		for ($i = 0; $i < $n; $i++) {
			$index = $candidates[$i];
			if ($this->isAttacker[$party[$index]] !== $this->isAttacker[$party[0]]) {
				return [];
			}
			$candidates[$i] = new Id($index);
		}
		return $candidates;
	}
}
