<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Factory\Model\Distribution;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat as CombatModel;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

class Combat extends CombatModel
{
	protected const BATTLE_ROWS = [self::REFUGEE, self::BYSTANDER, self::BACK, self::FRONT];

	protected const ROW_NAME = [self::REFUGEE => 'refugees', self::BYSTANDER => 'bystanders', self::BACK => 'back',
		                        self::FRONT => 'front'];

	protected const OVERRUN = 3.0;

	protected int $round = 0;

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
			default          => $battleRow
		};
	}

	public function __construct() {
		$this->attacker = array_fill_keys(self::BATTLE_ROWS, []);
		$this->defender = array_fill_keys(self::BATTLE_ROWS, []);
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

	public function addAttacker(Unit $unit): Combat {
		$army = $this->getArmy($unit);
		foreach ($army->add($unit)->Combatants() as $combatant) {
			$this->attacker[$combatant->BattleRow()][] = $combatant;
		}
		$this->isAttacker[$army->Party()->Id()->Id()] = true;
		return $this;
	}

	public function addDefender(Unit $unit): Combat {
		$army = $this->getArmy($unit);
		foreach ($army->add($unit)->Combatants() as $combatant) {
			$this->defender[$combatant->BattleRow()][] = $combatant;
		}
		$this->isAttacker[$army->Party()->Id()->Id()] = false;
		return $this;
	}

	public function tacticsRound(Party $party): Combat {
		$this->arrangeBattleRows();
		if ($this->isAttacker[$party->Id()->Id()]) {
			Lemuria::Log()->debug('Attacker gets first strike in tactics round.');
			$this->attack($this->attacker, $this->defender);
		} else {
			Lemuria::Log()->debug('Defender gets first strike in tactics round.');
			$this->attack($this->defender, $this->attacker);
		}
		return $this;
	}

	public function nextRound(): int {
		$this->arrangeBattleRows();
		$this->round++;
		Lemuria::Log()->debug('Combat round ' . $this->round . ' starts.');
		$damage  = $this->attack($this->attacker, $this->defender);
		$damage += $this->attack($this->defender, $this->attacker);
		return $damage;
	}

	protected function getArmy(Unit $unit): Army {
		$party = $unit->Party();
		$id    = $party->Id()->Id();
		if (!isset($this->armies[$id])) {
			$this->armies[$id] = new Army($party);
		}
		return $this->armies[$id];
	}

	/**
	 * @return array(int=>int)|int
	 */
	#[Pure] protected function countCombatants(array $side, ?int $row = null): array|int {
		if (is_int($row)) {
			$count = 0;
			foreach ($side[$row] as $combatant /* @var Combatant $combatant */) {
				$count += $combatant->Distribution()->Size();
			}
			return $count;
		}

		$count = array_fill_keys(self::BATTLE_ROWS, 0);
		foreach ($side as $row => $combatants) {
			foreach ($combatants as $combatant /* @var Combatant $combatant */) {
				$count[$row] += $combatant->Distribution()->Size();
			}
		}
		return $count;
	}

	protected function arrangeBattleRows(): void {
		$this->logSideDistribution();
		$attackers = $this->countCombatants($this->attacker, self::FRONT);
		$defenders = $this->countCombatants($this->defender, self::FRONT);
		$ratio = $defenders > 0 ? $attackers / $defenders : PHP_INT_MAX;
		if ($ratio > self::OVERRUN) {
			$additional = (int)ceil($attackers / self::OVERRUN) - $defenders;
			$side       = &$this->defender;
			$who        = 'Defender';
		} elseif ($ratio < 1.0 / self::OVERRUN) {
			$additional = (int)ceil($defenders / self::OVERRUN) - $attackers;
			$side       = &$this->attacker;
			$who        = 'Attacker';
		} else {
			return;
		}
		Lemuria::Log()->debug($who . ' is overrun (need ' . $additional . ' more in front row).');

		if ($additional > 0) {
			$additional = $this->arrangeFromRow($side, $who, self::BACK, $additional);
		}
		if ($additional > 0) {
			$additional = $this->arrangeFromRow($side, $who, self::BYSTANDER, $additional);
		}
		if ($additional > 0) {
			$additional = $this->arrangeFromRow($side, $who, self::REFUGEE, $additional);
		}
		if ($additional > 0) {
			$who = $ratio > 0.0 ? 'Defender' : 'Attacker';
			Lemuria::Log()->debug($who . ' has no more forces to reinforce the front.');
		}
		$this->logSideDistribution();
	}

	protected function attack(array &$attacker, array &$defender): int {
		//TODO
		Lemuria::Log()->debug('Attack is not implemented yet.');
		return 0;
	}

	protected function arrangeFromRow(array &$side, string $who, int $battleRow, int $additional): int {
		$n    = count($side[$battleRow]);
		$name = self::ROW_NAME[$battleRow];
		if ($n <= 0) {
			Lemuria::Log()->debug($who . ' has no combatants in ' . $name . ' row to send to the front.');
			return $additional;
		}

		for ($i = $n - 1; $i >= 0; $i--) {
			/** @var Combatant $combatant */
			$combatant    = $side[$battleRow][$i];
			$unit         = $combatant->Unit();
			$distribution = $combatant->Distribution();
			$size         = $distribution->Size();
			$weaponSkill  = Combatant::getWeaponSkill($unit, self::FRONT);
			if ($size <= $additional) {
				$side[self::FRONT][] = $combatant->setBattleRow(self::FRONT);
				unset($side[$battleRow][$i]);
				$additional -= $size;
				Lemuria::Log()->debug($who . ' ' . $unit . ' sends combatant ' . $i . ' (size ' . $size . ') from ' . $name . ' row to the front.');
				if ($additional <= 0) {
					break;
				}
			} else {
				$newDistribution = new Distribution();
				foreach ($distribution as $quantity /* @var Quantity $quantity */) {
					$count = (int)round($additional * $quantity->Count() / $size);
					$newDistribution->add(new Quantity($quantity->Commodity(), $count));
				}
				foreach ($newDistribution as $quantity /* @var Quantity $quantity */) {
					$distribution->remove(new Quantity($quantity->Commodity(), $quantity->Count()));
				}
				$newDistribution->setSize($additional);
				$newCombatant = new Combatant($unit);
				$newCombatant->setBattleRow(self::FRONT)->setWeapon($weaponSkill)->setDistribution($distribution);
				$distribution->setSize($size - $additional);
				$additional = 0;
				Lemuria::Log()->debug($who . ' ' . $unit . ' sends ' . $additional . ' persons from combatant ' . $i . ' in ' . $name . ' row to the front.');
				break;
			}
		}
		return $additional;
	}

	protected function logSideDistribution(): void {
		$attacker = [];
		foreach ($this->attacker as $row => $combatants) {
			$count = 0;
			foreach ($combatants as $combatant /* @var Combatant $combatant */) {
				$count += $combatant->Distribution()->Size();
			}
			$attacker[] = self::ROW_NAME[$row] . ':' . $count;
		}
		$defender = [];
		foreach ($this->defender as $row => $combatants) {
			$count = 0;
			foreach ($combatants as $combatant /* @var Combatant $combatant */) {
				$count += $combatant->Distribution()->Size();
			}
			$defender[] = self::ROW_NAME[$row] . ':' . $count;
		}
		Lemuria::Log()->debug('Combatant distribution is ' . implode(' | ', $attacker) . ' vs. ' . implode(' | ', $defender) . '.');
	}
}
