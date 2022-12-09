<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Model\Fantasya\Combat\BattleRow;

class Rank implements \ArrayAccess, \Countable, \Iterator
{
	public final const FRONT = BattleRow::Front->value;

	public final const BACK = BattleRow::Back->value;

	public final const BYSTANDER = BattleRow::Bystander->value;

	public final const REFUGEE = BattleRow::Refugee->value;

	/**
	 * @var Combatant[]
	 */
	protected array $combatants = [];

	private int $index = 0;

	private bool $isUnset = false;

	public function Size(): int {
		$size = 0;
		foreach ($this->combatants as $combatant) {
			$size += $combatant->Size();
		}
		return $size;
	}

	public function Hits(): int {
		$hits = 0;
		foreach ($this->combatants as $combatant) {
			$hits += $combatant->Size() * $combatant->Weapon()->Hits();
		}
		return $hits;
	}

	/**
	 * @param int $offset
	 * @return bool
	 */
	public function offsetExists(mixed $offset): bool {
		return isset($this->combatants[$offset]);
	}

	/**
	 * @param int $offset
	 * @return Combatant
	 */
	public function offsetGet(mixed $offset): Combatant {
		return $this->combatants[$offset];
	}

	/**
	 * @param int $offset
	 * @param Combatant $value
	 */
	public function offsetSet(mixed $offset, mixed $value): void {
		$this->combatants[$offset] = $value;
	}

	public function offsetUnset(mixed $offset): void {
		unset($this->combatants[$offset]);
		$this->combatants = array_values($this->combatants);
		if ($offset <= $this->index) {
			if ($this->index > 0) {
				$this->index--;
			} else {
				$this->isUnset = true;
			}
		}
	}

	public function count(): int {
		return count($this->combatants);
	}

	public function current(): Combatant {
		return $this->combatants[$this->index];
	}

	public function key(): int {
		return $this->index;
	}

	public function next(): void {
		if ($this->isUnset) {
			$this->isUnset = false;
		} else {
			$this->index++;
		}
	}

	public function rewind(): void {
		$this->index = 0;
	}

	public function valid(): bool {
		return $this->index < count($this->combatants);
	}

	public function add(Combatant $combatant): void {
		$this->combatants[] = $combatant;
	}
}
