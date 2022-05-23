<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Exception\LemuriaException;

class Ranks implements \ArrayAccess, \Countable, \Iterator
{
	public final const BATTLE_ROWS = [Rank::REFUGEE, Rank::BYSTANDER, Rank::BACK, Rank::FRONT];

	/**
	 * @var Rank[]
	 */
	protected array $ranks;

	protected Effects $effects;

	private int $index = 0;

	private int $count;

	public function __construct(protected bool $isAttacker) {
		$this->ranks = [];
		foreach (self::BATTLE_ROWS as $row) {
			$this->ranks[$row] = new Rank();
		}
		$this->count   = count(self::BATTLE_ROWS);
		$this->effects = new Effects();
	}

	public function IsAttacker(): bool {
		return $this->isAttacker;
	}

	public function IsDefender(): bool {
		return !$this->isAttacker;
	}

	public function Effects(): Effects {
		return $this->effects;
	}

	/**
	 * @param int $offset
	 * @return bool
	 */
	public function offsetExists(mixed $offset): bool {
		return isset($this->ranks[$offset]);
	}

	/**
	 * @param int $offset
	 * @return Rank
	 */
	public function offsetGet(mixed $offset): Rank {
		return $this->ranks[$offset];
	}

	/**
	 * @param int $offset
	 * @param Rank $value
	 */
	public function offsetSet(mixed $offset, mixed $value): void {
		throw new LemuriaException('Setting a rank is not allowed.');
	}

	/**
	 * @param int $offset
	 */
	public function offsetUnset(mixed $offset): void {
		throw new LemuriaException('Unsetting a rank is not allowed.');
	}

	public function count(): int {
		$count = 0;
		foreach ($this->ranks as $rank) {
			$count += $rank->count();
		}
		return $count;
	}

	/**
	 * @return Rank
	 */
	public function current(): mixed {
		return $this->ranks[self::BATTLE_ROWS[$this->index]];
	}

	/**
	 * @return int
	 */
	public function key(): mixed {
		return self::BATTLE_ROWS[$this->index];
	}

	public function next(): void {
		$this->index++;
	}

	public function rewind(): void {
		$this->index = 0;
	}

	public function valid(): bool {
		return $this->index < $this->count;
	}

	public function add(Combatant $combatant): Ranks {
		$this->ranks[$combatant->BattleRow()->value]->add($combatant);
		return $this;
	}
}
