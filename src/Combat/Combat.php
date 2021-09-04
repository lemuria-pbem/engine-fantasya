<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Factory\Model\Distribution;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat as CombatModel;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

class Combat extends CombatModel
{
	public const FLIGHT = [
		self::REFUGEE    => 1.0,
		self::BYSTANDER  => 0.9, self::DEFENSIVE => 0.9,
		self::BACK       => 0.2, self::FRONT     => 0.2,
		self::AGGRESSIVE => 0.0
	];

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

	public function addAttacker(Unit $unit): Army {
		$army = $this->getArmy($unit);
		foreach ($army->add($unit)->Combatants() as $combatant) {
			$this->attacker[$combatant->BattleRow()][] = $combatant;
		}
		$this->isAttacker[$army->Party()->Id()->Id()] = true;
		return $army;
	}

	public function addDefender(Unit $unit): Army {
		$army = $this->getArmy($unit);
		foreach ($army->add($unit)->Combatants() as $combatant) {
			$this->defender[$combatant->BattleRow()][] = $combatant;
		}
		$this->isAttacker[$army->Party()->Id()->Id()] = false;
		return $army;
	}

	public function tacticsRound(Party $party): Combat {
		$this->everybodyTryToFlee();
		if ($this->arrangeBattleRows()) {
			$this->fleeFromBattle($this->attacker[self::REFUGEE], 'Attacker', true);
			$this->fleeFromBattle($this->defender[self::REFUGEE], 'Defender', true);
			if ($this->isAttacker[$party->Id()->Id()]) {
				Lemuria::Log()->debug('Attacker gets first strike in tactics round.');
				$this->attack($this->attacker, $this->defender, 'Attacker');
			} else {
				Lemuria::Log()->debug('Defender gets first strike in tactics round.');
				$this->attack($this->defender, $this->attacker, 'Defender');
			}
		} else {
			Lemuria::Log()->debug('Everyone has fled before the battle could begin.');
		}
		return $this;
	}

	public function nextRound(): int {
		$this->everybodyTryToFlee();
		if ($this->arrangeBattleRows()) {
			$this->fleeFromBattle($this->attacker[self::REFUGEE], 'Attacker', true);
			$this->fleeFromBattle($this->defender[self::REFUGEE], 'Defender', true);
			$this->round++;
			Lemuria::Log()->debug('Combat round ' . $this->round . ' starts.');
			$damage = $this->attack($this->attacker, $this->defender, 'Attacker');
			$damage += $this->attack($this->defender, $this->attacker, 'Defender');
			Lemuria::Log()->debug($damage . ' damage done in round ' . $this->round . '.');
			return $damage;
		} else {
			Lemuria::Log()->debug('Everyone has fled and the battle has ended.');
			return 0;
		}
	}

	protected function getArmy(Unit $unit): Army {
		$party = $unit->Party();
		$id    = $party->Id()->Id();
		if (!isset($this->armies[$id])) {
			$this->armies[$id] = new Army($party);
		}
		return $this->armies[$id];
	}

	#[Pure] protected function countRowCombatants(array $side, int $row): int {
		$count = 0;
		foreach ($side[$row] as $combatant /* @var Combatant $combatant */) {
			$count += $combatant->Size();
		}
		return $count;
	}

	protected function countCombatants(array $side, bool $isAttack = false): int {
		$count = 0;
		foreach ($side as $combatant /* @var Combatant $combatant */) {
			if ($isAttack) {
				$count += $combatant->Size() * $combatant->Hits();
			} else {
				$count += $combatant->Size();
			}
		}
		return $count;
	}

	protected function logSideDistribution(): void {
		$attacker = [];
		foreach ($this->attacker as $row => $combatants) {
			$count = 0;
			foreach ($combatants as $combatant /* @var Combatant $combatant */) {
				$count += $combatant->Size();
			}
			$attacker[] = self::ROW_NAME[$row] . ':' . $count;
		}
		$defender = [];
		foreach ($this->defender as $row => $combatants) {
			$count = 0;
			foreach ($combatants as $combatant /* @var Combatant $combatant */) {
				$count += $combatant->Size();
			}
			$defender[] = self::ROW_NAME[$row] . ':' . $count;
		}
		Lemuria::Log()->debug('Combatant distribution is ' . implode(' | ', $attacker) . ' vs. ' . implode(' | ', $defender) . '.');
	}

	protected function arrangeBattleRows(): bool {
		$this->logSideDistribution();
		$attackers = $this->countRowCombatants($this->attacker, self::FRONT);
		$defenders = $this->countRowCombatants($this->defender, self::FRONT);
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
			return true;
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
		return true;
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
			$size         = $combatant->Size();
			if ($size <= $additional) {
				$side[self::FRONT][] = $combatant->setBattleRow(self::FRONT);
				unset($side[$battleRow][$i]);
				$additional -= $size;
				Lemuria::Log()->debug($who . ' ' . $unit . ' sends combatant ' . $combatant->Id() . ' (size ' . $size . ') from ' . $name . ' row to the front.');
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
				$newCombatant = new Combatant($combatant->Army(), $unit);
				$newCombatant->setBattleRow(self::FRONT)->setDistribution($distribution);
				$distribution->setSize($size - $additional);
				$additional = 0;
				$combatant->Army()->addCombatant($newCombatant);
				Lemuria::Log()->debug($who . ' ' . $unit . ' sends ' . $additional . ' persons from combatant ' . $combatant->Id() . ' in ' . $name . ' row to the front as combatant ' . $newCombatant->Id() . '.');
				break;
			}
		}
		return $additional;
	}

	protected function everybodyTryToFlee(): void {
		$this->fleeIfWounded($this->attacker[self::FRONT], 'Attacker');
		$this->fleeIfWounded($this->attacker[self::BACK], 'Attacker');
		$this->fleeIfWounded($this->attacker[self::BYSTANDER], 'Attacker');
		$this->fleeFromBattle($this->attacker[self::REFUGEE], 'Attacker');
		$this->fleeIfWounded($this->defender[self::FRONT], 'Defender');
		$this->fleeIfWounded($this->defender[self::BACK], 'Defender');
		$this->fleeIfWounded($this->defender[self::BYSTANDER], 'Defender');
		$this->fleeFromBattle($this->defender[self::REFUGEE], 'Defender');
	}

	protected function fleeIfWounded(array &$combatantRow, string $who): void {
		$hasChanges = false;
		foreach (array_keys($combatantRow) as $i) {
			/** @var Combatant $combatant */
			$combatant    = $combatantRow[$i];
			$calculus     = new Calculus($combatant->Unit());
			$minHitpoints = (int)ceil($calculus->hitpoints() * $combatant->Flight());
			foreach ($combatant->fighters as $f => $fighter) {
				if ($fighter->health < $minHitpoints) {
					if (randChance($combatant->FlightChance())) {
						$combatant->flee($f);
						$hasChanges = true;
						Lemuria::Log()->debug($who . ' fighter ' . $combatant->getId($f) . ' flees from battle.');
					}
				}
			}
			if ($combatant->Size() <= 0) {
				$combatant->flee();
				unset($combatantRow[$i]);
				Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' has fled from battle.');
			}
		}
		if ($hasChanges) {
			$combatantRow = array_values($combatantRow);
		}
	}

	protected function fleeFromBattle(array &$combatantRow, string $who, $fleeSuccessfully = false): void {
		$hasChanges = false;
		foreach (array_keys($combatantRow) as $i) {
			/** @var Combatant $combatant */
			$combatant = $combatantRow[$i];
			if ($fleeSuccessfully) {
				unset($combatantRow[$i]);
				$hasChanges = true;
				Lemuria::Log()->debug($who . ' combatant ' . $combatant->Id() . ' flees from battle.');
			} elseif (randChance($combatant->FlightChance())) {
				unset($combatantRow[$i]);
				$hasChanges = true;
				Lemuria::Log()->debug($who . ' combatant ' . $combatant->Id() . ' managed to flee from battle.');
			} else {
				Lemuria::Log()->debug($who . ' combatant ' . $combatant->Id() . ' tried to flee from battle.');
			}
		}
		if ($hasChanges) {
			$combatantRow = array_values($combatantRow);
		}
	}

	protected function attack(array &$attacker, array &$defender, string $who): int {
		$message = $who . ': Front row attacks.';
		$damage  = $this->attackRowAgainstRow($attacker[self::FRONT], $defender[self::FRONT], $message);
		$message = $who . ': Back row attacks.';
		$damage += $this->attackRowAgainstRow($attacker[self::BACK], $defender[self::FRONT], $message);
		$this->removeTheDead($attacker);
		$this->removeTheDead($defender);
		return $damage;
	}

	protected function attackRowAgainstRow(array $attacker, array $defender, string $message): int {
		$a  = count($attacker);
		$a1 = $this->countCombatants($attacker, true);
		if ($a <= 0 || $a1 <= 0) {
			return 0;
		}
		Lemuria::Log()->debug($message);

		$damage = 0;
		$d      = count($defender);
		$d1     = $this->countCombatants($defender);
		$rate   = $d1 / $a1;
		$nextA  = 0;
		$nextD  = 0;
		$sum    = 0;
		$last   = 0;
		$cA     = -1;
		$fA     = 0;
		$nA     = 0;
		$hit    = 0;
		$hits   = 1;
		$cD     = -1;
		$fD     = 0;
		$nD     = 0;
		$comA   = null;
		$comD   = null;
		while ($cA < $a && $cD < $d) {
			if ($fA >= $nA) {
				/** @var Combatant $comA */
				$comA = $attacker[++$cA] ?? null;
				$nA   = $comA?->Size();
				$hits = $comA?->Hits();
				$fA   = 0;
				$hit  = 0;
				continue;
			}
			if ($fD >= $nD) {
				$sum += $nD;
				/** @var Combatant $comD */
				$comD = $defender[++$cD] ?? null;
				$nD   = $comD?->Size();
				$last = $sum + $nD;
				$fD   = $nextD - $sum;
				continue;
			}
			if ($nextD >= $last) {
				$fD = $nD;
				continue;
			}

			if ($comA->fighters[$fA]->health > 0) {
				$health = $comD->fighters[$fD]->health;
				$damage = $comD->assault($fD, $comA, $fA);
				if ($damage >= $health) {
					Lemuria::Log()->debug('Enemy ' . $comD->getId($fD) . ' is dead.');
				}
			} else {
				Lemuria::Log()->debug('Fighter ' . $comA->getId($fA) . ' is dead.');
			}

			if ($hit++ >= $hits) {
				$fA++;
				$hit = 0;
			}
			$nextD = (int)floor(++$nextA * $rate);
		}

		return $damage;
	}

	protected function removeTheDead(array &$combatantRows): void {
		foreach ($combatantRows as &$combatants) {
			foreach ($combatants as $c => &$combatant /* @var Combatant $combatant */) {
				$size = $combatant->Size();
				foreach ($combatant->fighters as $f => $fighter) {
					if ($fighter->health <= 0) {
						unset($combatant->fighters[$f]);
					}
				}
				$deceased = $size - $combatant->Size();
				if ($deceased > 0) {
					$unit = $combatant->Unit();
					$unit->setSize($unit->Size() - $deceased);
					$inventory = $unit->Inventory();
					foreach ($combatant->Distribution()->lose($deceased) as $quantity /* @var Quantity $quantity*/) {
						$inventory->remove($quantity);
						$combatant->Army()->Loss()->add(new Quantity($quantity->Commodity(), $quantity->Count()));
						Lemuria::Log()->debug('Unit ' . $unit . ' loses ' . $quantity . '.');
					}
					if ($combatant->Size() <= 0) {
						unset($combatants[$c]);
						$combatants = array_values($combatants);
						$unit->setIsGuarding(false)->setIsHiding(false);
						Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' was wiped out.');
					} else {
						$combatant->fighters = array_values($combatant->fighters);
						Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' has ' . $deceased . ' losses.');
					}
				}
			}
		}
	}
}
