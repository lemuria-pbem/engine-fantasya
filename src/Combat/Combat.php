<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Model\Fantasya\Combat as CombatModel;
use Lemuria\Model\Fantasya\Unit;

class Combat extends CombatModel
{
	protected const BATTLE_ROWS = [self::REFUGEE, self::BYSTANDER, self::BACK, self::FRONT];

	/**
	 * @var array(int=>array)
	 */
	protected array $attacker;

	/**
	 * @var array(int=>array)
	 */
	protected array $defender;

	/**
	 * @var array(int=>bool)
	 */
	protected array $isAttacker = [];

	/**
	 * @var array(int=>Army)
	 */
	protected array $armies = [];

	#[Pure] public static function getBattleRow(Unit $unit): int {
		$battleRow = $unit->BattleRow();
		return match ($battleRow) {
			self::DEFENSIVE  => self::BACK,
			self::AGGRESSIVE => self::FRONT,
			default => $battleRow
		};
	}

	public function __construct() {
		$this->attacker = array_fill_keys(self::BATTLE_ROWS, []);
		$this->defender = array_fill_keys(self::BATTLE_ROWS, []);
	}

	public function addAttacker(Unit $unit): Combat {
		$army = $this->getArmy($unit);
		$row  = self::getBattleRow($unit);
		foreach ($army->add($unit)->Combatants() as $combatant) {
			$this->attacker[$row][] = $combatant;
		}
		$this->isAttacker[$army->Party()->Id()->Id()] = true;
		return $this;
	}

	public function addDefender(Unit $unit): Combat {
		$army = $this->getArmy($unit);
		$row  = self::getBattleRow($unit);
		foreach ($army->add($unit)->Combatants() as $combatant) {
			$this->defender[$row][] = $combatant;
		}
		$this->isAttacker[$army->Party()->Id()->Id()] = false;
		return $this;
	}

	public function hasAttackers(): bool {
		foreach (self::BATTLE_ROWS as $row) {
			if ($this->attacker[$row]) {
				return true;
			}
		}
		return false;
	}

	public function hasDefenders(): bool {
		foreach (self::BATTLE_ROWS as $row) {
			if ($this->defender[$row]) {
				return true;
			}
		}
		return false;
	}

	public function tacticsRound(Unit $unit): Combat {
		$this->arrangeBattleRows();
		if ($this->isAttacker[$this->getArmy($unit)->Party()->Id()->Id()]) {
			$this->attack($this->attacker, $this->defender);
		} else {
			$this->attack($this->defender, $this->attacker);
		}
		return $this;
	}

	public function nextRound(): Combat {
		$this->arrangeBattleRows();
		$this->attack($this->attacker, $this->defender);
		$this->attack($this->defender, $this->attacker);
		return $this;
	}

	protected function getArmy(Unit $unit): Army {
		$party = $unit->Party();
		$id    = $party->Id()->Id();
		if (!isset($this->armies[$id])) {
			$this->armies[$id] = new Army($party);
		}
		return $this->armies[$id];
	}

	protected function arrangeBattleRows(): void {
		//TODO
	}

	protected function attack(array &$attacker, array &$defender): void {
		//TODO
	}
}
