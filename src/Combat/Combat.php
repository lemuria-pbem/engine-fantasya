<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Log\Entity;
use Lemuria\Engine\Fantasya\Combat\Log\Message;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AttackerHasFledMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AttackerNoReinforcementMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AttackerOverrunMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AttackerReinforcementMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AttackerReinforcementWeaponMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AttackerSideMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AttackerSplitMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AttackerSplitWeaponMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\AttackerTacticsRoundMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\CombatantNoWeaponMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\CombatantWeaponMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\CombatRoundMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\DefenderHasFledMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\DefenderNoReinforcementMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\DefenderOverrunMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\DefenderReinforcementMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\DefenderReinforcementWeaponMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\DefenderSideMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\DefenderSplitMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\DefenderSplitWeaponMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\DefenderTacticsRoundMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\EveryoneHasFledMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\FighterCouldNotFleeMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\FighterFleesMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\FighterIsDeadMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\FighterSavedMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\FleeFromBattleMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\ManagedToFleeFromBattleMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\TriedToFleeFromBattleMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Participant;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\Model\BattleSpellGrade;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat as CombatModel;
use Lemuria\Model\Fantasya\Commodity\Potion\HealingPotion;
use Lemuria\Model\Fantasya\Commodity\Weapon\Native;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

class Combat extends CombatModel
{
	public const ROW_NAME = [self::REFUGEE => 'refugees', self::BYSTANDER => 'bystanders', self::BACK => 'back',
							 self::FRONT   => 'front'];

	protected const BATTLE_ROWS = [self::REFUGEE, self::BYSTANDER, self::BACK, self::FRONT];

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

	/**
	 * @var Participant[]
	 */
	protected array $attackParticipants = [];

	/**
	 * @var Participant[]
	 */
	protected array $defendParticipants = [];

	protected Effects $effects;

	#[Pure] public static function getBattleRow(Unit $unit): int {
		$battleRow = $unit->BattleRow();
		return match ($battleRow) {
			self::DEFENSIVE                 => self::BACK,
			self::CAREFUL, self::AGGRESSIVE => self::FRONT,
			default                         => $battleRow
		};
	}

	#[Pure] public function __construct(private Context $context) {
		$this->attacker = array_fill_keys(self::BATTLE_ROWS, []);
		$this->defender = array_fill_keys(self::BATTLE_ROWS, []);
		$this->effects  = new Effects();
	}

	public function Effects(): Effects {
		return $this->effects;
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
		$this->attackParticipants[] = new Participant(new Entity($unit), $army->Combatants());
		return $army;
	}

	public function addDefender(Unit $unit): Army {
		$army = $this->getArmy($unit);
		foreach ($army->add($unit)->Combatants() as $combatant) {
			$this->defender[$combatant->BattleRow()][] = $combatant;
		}
		$this->isAttacker[$army->Party()->Id()->Id()] = false;
		$this->defendParticipants[] = new Participant(new Entity($unit), $army->Combatants());
		return $army;
	}

	public function embattle(): Combat {
		$log = BattleLog::getInstance();
		$log->add(new AttackerSideMessage($this->attackParticipants));
		foreach ($this->attacker as $combatants) {
			foreach ($combatants as $combatant /* @var Combatant $combatant */) {
				$log->add($this->getCombatantMessage($combatant));
			}
		}
		$log->add(new DefenderSideMessage($this->defendParticipants));
		foreach ($this->defender as $combatants) {
			foreach ($combatants as $combatant /* @var Combatant $combatant */) {
				$log->add($this->getCombatantMessage($combatant));
			}
		}
		return $this;
	}

	public function castPreparationSpells(?Party $first): Combat {
		if ($first) {
			if ($this->isAttacker[$first->Id()->Id()]) {
				$units = $this->prepareBattleSpells($this->attacker);
				$this->castPreparationSpell($units, $this->attacker, $this->defender);
				$units = $this->prepareBattleSpells($this->defender);
				$this->castPreparationSpell($units, $this->defender, $this->attacker);
			} else {
				$units = $this->prepareBattleSpells($this->defender);
				$this->castPreparationSpell($units, $this->defender, $this->attacker);
				$units = $this->prepareBattleSpells($this->attacker);
				$this->castPreparationSpell($units, $this->attacker, $this->defender);
			}
		} else {
			$attacker = $this->prepareBattleSpells($this->attacker);
			$defender = $this->prepareBattleSpells($this->defender);
			$this->castPreparationSpell($attacker, $this->attacker, $this->defender);
			$this->castPreparationSpell($defender, $this->defender, $this->attacker);
		}
		return $this;
	}

	public function tacticsRound(Party $party): Combat {
		$this->everybodyTryToFlee();
		if ($this->arrangeBattleRows()) {
			$this->fleeFromBattle($this->attacker[self::REFUGEE], 'Attacker', true);
			$this->fleeFromBattle($this->defender[self::REFUGEE], 'Defender', true);
			if ($this->isAttacker[$party->Id()->Id()]) {
				Lemuria::Log()->debug('Attacker gets first strike in tactics round.');
				BattleLog::getInstance()->add(new AttackerTacticsRoundMessage());
				$this->attack($this->attacker, $this->defender, 'Attacker');
			} else {
				Lemuria::Log()->debug('Defender gets first strike in tactics round.');
				BattleLog::getInstance()->add(new DefenderTacticsRoundMessage());
				$this->attack($this->defender, $this->attacker, 'Defender');
			}
		} else {
			Lemuria::Log()->debug('Everyone has fled before the battle could begin.');
			BattleLog::getInstance()->add(new EveryoneHasFledMessage());
		}
		return $this;
	}

	public function nextRound(): int {
		$this->everybodyTryToFlee();
		if ($this->arrangeBattleRows()) {
			$this->fleeFromBattle($this->attacker[self::REFUGEE], 'Attacker', true);
			$this->fleeFromBattle($this->defender[self::REFUGEE], 'Defender', true);
			$this->round++;
			BattleLog::getInstance()->add(new CombatRoundMessage($this->round));
			$this->castCombatSpells();
			$damage  = $this->attack($this->attacker, $this->defender, 'Attacker');
			$damage += $this->attack($this->defender, $this->attacker, 'Defender');
			Lemuria::Log()->debug($damage . ' damage done in round ' . $this->round . '.');
			return $damage;
		} else {
			if ($this->hasAttackers()) {
				Lemuria::Log()->debug('All defenders have fled and the battle has ended.');
				BattleLog::getInstance()->add(new DefenderHasFledMessage());
			} elseif ($this->hasDefenders()) {
				Lemuria::Log()->debug('All attackers have fled and the battle has ended.');
				BattleLog::getInstance()->add(new AttackerHasFledMessage());
			} else {
				Lemuria::Log()->debug('Everyone has fled and the battle has ended.');
				BattleLog::getInstance()->add(new EveryoneHasFledMessage());
			}
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

	#[Pure] protected function countCombatants(array $side, bool $isAttack = false): int {
		$count = 0;
		foreach ($side as $combatant /* @var Combatant $combatant */) {
			if ($isAttack) {
				$count += $combatant->Size() * $combatant->Weapon()->Hits();
			} else {
				$count += $combatant->Size();
			}
		}
		return $count;
	}

	/**
	 * @return Unit[]
	 */
	protected function prepareBattleSpells(array $caster): array {
		$units = [];
		foreach ($caster[self::FRONT] as $combatant /* @var Combatant $combatant */) {
			$unit = $combatant->Unit();
			if ($unit->BattleSpells()?->Preparation()) {
				$units[$unit->Id()->Id()] = $unit;
			}
		}
		foreach ($caster[self::BACK] as $combatant /* @var Combatant $combatant */) {
			$unit = $combatant->Unit();
			if ($unit->BattleSpells()?->Preparation()) {
				$units[$unit->Id()->Id()] = $unit;
			}
		}
		return array_values($units);
	}

	/**
	 * @param Unit[] $units
	 */
	protected function castPreparationSpell(array $units, array $caster, array $victim): void {
		foreach ($units as $unit) {
			$grade = new BattleSpellGrade($unit->BattleSpells()->Preparation(), $this);
			$spell = $this->context->Factory()->castBattleSpell($grade);
			$grade = $spell->setCaster($caster)->setVictim($victim)->cast($unit);
			if ($grade > 0) {
				$this->setHasCast($unit, $caster[self::FRONT]);
				$this->setHasCast($unit, $caster[self::BACK]);
			}
		}
	}

	/**
	 * @param Combatant[] $combatants
	 */
	protected function setHasCast(Unit $unit, array $combatants): void {
		foreach ($combatants as $combatant) {
			if ($combatant->Unit() === $unit) {
				$combatant->hasCast = true;
			}
		}
	}

	#[Pure] protected function getCombatantMessage(Combatant $combatant): Message {
		if ($combatant->Weapon() instanceof Native) {
			return new CombatantNoWeaponMessage($combatant);
		}
		return new CombatantWeaponMessage($combatant);
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
		$attackers = $this->countRowCombatants($this->attacker, self::FRONT);
		$defenders = $this->countRowCombatants($this->defender, self::FRONT);
		if ($attackers > 0 || $defenders > 0) {
			$ratio = $defenders > 0 ? $attackers / $defenders : PHP_INT_MAX;
			if ($ratio > self::OVERRUN) {
				$additional = (int)ceil($attackers / self::OVERRUN) - $defenders;
				BattleLog::getInstance()->add(new DefenderOverrunMessage($additional));
				$this->arrangeRows($additional, $this->defender, false);
			} elseif ($ratio < 1.0 / self::OVERRUN) {
				$additional = (int)ceil($defenders / self::OVERRUN) - $attackers;
				BattleLog::getInstance()->add(new AttackerOverrunMessage($additional));
				$this->arrangeRows($additional, $this->attacker, true);
			} else {
				return true;
			}
		} else {
			BattleLog::getInstance()->add(new AttackerOverrunMessage(1));
			$this->arrangeRows(1, $this->attacker, true);
			BattleLog::getInstance()->add(new DefenderOverrunMessage(1));
			$this->arrangeRows(1, $this->defender, false);
		}
		$this->logSideDistribution();
		return $this->hasAttackers() && $this->hasDefenders();
	}

	protected function arrangeRows(int $additional, array &$side, $isAttacker): void {
		if ($additional > 0) {
			$additional = $this->arrangeFromRow($side, $isAttacker, self::BACK, $additional);
		}
		if ($additional > 0) {
			$additional = $this->arrangeFromRow($side, $isAttacker, self::BYSTANDER, $additional);
		}
		if ($additional > 0) {
			$additional = $this->arrangeFromRow($side, $isAttacker, self::REFUGEE, $additional);
		}
		if ($additional > 0) {
			if ($isAttacker) {
				BattleLog::getInstance()->add(new AttackerNoReinforcementMessage());
			} else {
				BattleLog::getInstance()->add(new DefenderNoReinforcementMessage());
			}
		}
	}

	protected function arrangeFromRow(array &$side, bool $isAttacker, int $battleRow, int $additional): int {
		$n    = count($side[$battleRow]);
		$name = self::ROW_NAME[$battleRow];
		if ($n <= 0) {
			return $additional;
		}

		for ($i = $n - 1; $i >= 0; $i--) {
			/** @var Combatant $combatant */
			$combatant = $side[$battleRow][$i];
			$unit      = $combatant->Unit();
			$size      = $combatant->Size();
			if ($size <= $additional) {
				$side[self::FRONT][] = $combatant->setBattleRow(self::FRONT);
				unset($side[$battleRow][$i]);
				$additional -= $size;
				$who         = $isAttacker ? 'Attacker' : 'Defender';
				Lemuria::Log()->debug($who . ' ' . $unit . ' sends combatant ' . $combatant->Id() . ' (size ' . $size . ') from ' . $name . ' row to the front.');
				$hasWeapon = !($combatant->Weapon() instanceof Native);
				if ($isAttacker) {
					$message = $hasWeapon ?
						new AttackerReinforcementWeaponMessage(new Entity($unit), $combatant, $size, $battleRow) :
						new AttackerReinforcementMessage(new Entity($unit), $combatant, $size, $battleRow);
				} else {
					$message = $hasWeapon ?
						new DefenderReinforcementWeaponMessage(new Entity($unit), $combatant, $size, $battleRow) :
						new DefenderReinforcementMessage(new Entity($unit), $combatant, $size, $battleRow);
				}
				BattleLog::getInstance()->add($message);
				if ($additional <= 0) {
					break;
				}
			} else {
				$newCombatant = $combatant->split($additional);
				$combatant->Army()->addCombatant($newCombatant);
				$side[self::FRONT][] = $newCombatant;
				$who                 = $isAttacker ? 'Attacker' : 'Defender';
				$hasWeapon           = !($newCombatant->Weapon() instanceof Native);
				Lemuria::Log()->debug($who . ' ' . $unit . ' sends ' . $additional . ' persons from combatant ' . $combatant->Id() . ' in ' . $name . ' row to the front as combatant ' . $newCombatant->Id() . '.');
				if ($isAttacker) {
					$message = $hasWeapon ?
						new AttackerSplitWeaponMessage(new Entity($unit), $combatant, $newCombatant, $additional, $battleRow) :
						new AttackerSplitMessage(new Entity($unit), $combatant, $newCombatant, $additional, $battleRow);
				} else {
					$message = $hasWeapon ?
						new DefenderSplitWeaponMessage(new Entity($unit), $combatant, $newCombatant, $additional, $battleRow) :
						new DefenderSplitMessage(new Entity($unit), $combatant, $newCombatant, $additional, $battleRow);
				}
				BattleLog::getInstance()->add($message);
				$additional = 0;
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
			$combatant = $combatantRow[$i];
			foreach ($combatant->fighters as $f => $fighter) {
				$chance = $combatant->isFleeing($fighter);
				if (is_float($chance)) {
					if ($chance >= 0.0) {
						$combatant->flee($f);
						$hasChanges = true;
						Lemuria::Log()->debug($who . ' fighter ' . $combatant->getId($f) . ' is wounded and flees from battle (chance: ' . $chance . ').');
						BattleLog::getInstance()->add(new FighterFleesMessage($combatant->getId($f)));
					} else {
						// Lemuria::Log()->debug($who . ' fighter ' . $combatant->getId($f) . ' is wounded, but could not flee from battle (chance: ' . -$chance . ').');
						BattleLog::getInstance()->add(new FighterCouldNotFleeMessage($combatant->getId($f)));
					}
				}
			}
			if ($combatant->Size() <= 0) {
				$combatant->flee();
				unset($combatantRow[$i]);
				// Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' has fled from battle.');
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
				BattleLog::getInstance()->add(new FleeFromBattleMessage($combatant));
			} else {
				$chance = $combatant->canFlee();
				if ($chance >= 0.0) {
					unset($combatantRow[$i]);
					$hasChanges = true;
					Lemuria::Log()->debug($who . ' combatant ' . $combatant->Id() . ' managed to flee from battle (chance: ' . $chance . ').');
					BattleLog::getInstance()->add(new ManagedToFleeFromBattleMessage($combatant));
				} else {
					Lemuria::Log()->debug($who . ' combatant ' . $combatant->Id() . ' tried to flee from battle (chance: ' . -$chance . ').');
					BattleLog::getInstance()->add(new TriedToFleeFromBattleMessage($combatant));
				}
			}
		}
		if ($hasChanges) {
			$combatantRow = array_values($combatantRow);
		}
	}

	protected function castCombatSpells(): void {
		$attacker = $this->prepareCombatSpells($this->attacker);
		$defender = $this->prepareCombatSpells($this->defender);
		$this->castCombatSpell($attacker, $this->attacker, $this->defender);
		$this->castCombatSpell($defender, $this->defender, $this->attacker);
	}

	/**
	 * @return Unit[]
	 */
	protected function prepareCombatSpells(array $caster): array {
		$units = [];
		foreach ($caster[self::FRONT] as $combatant /* @var Combatant $combatant */) {
			$unit = $combatant->Unit();
			if ($unit->BattleSpells()?->Combat()) {
				$units[$unit->Id()->Id()] = $unit;
			}
		}
		foreach ($caster[self::BACK] as $combatant /* @var Combatant $combatant */) {
			$unit = $combatant->Unit();
			if ($unit->BattleSpells()?->Combat()) {
				$units[$unit->Id()->Id()] = $unit;
			}
		}
		return array_values($units);
	}

	/**
	 * @param Unit[] $units
	 */
	protected function castCombatSpell(array $units, array $caster, array $victim): void {
		foreach ($units as $unit) {
			$grade = new BattleSpellGrade($unit->BattleSpells()->Combat(), $this);
			$spell = $this->context->Factory()->castBattleSpell($grade);
			$grade = $spell->setCaster($caster)->setVictim($victim)->cast($unit);
			if ($grade > 0) {
				$this->setHasCast($unit, $caster[self::FRONT]);
				$this->setHasCast($unit, $caster[self::BACK]);
			}
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

	/**
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function attackRowAgainstRow(array $attacker, array $defender, string $message): int {
		$a  = count($attacker);
		$a1 = $this->countCombatants($attacker, true);
		if ($a <= 0 || $a1 <= 0) {
			return 0;
		}
		// Lemuria::Log()->debug($message);

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
			/** @var Combatant $comA */
			if ($comA?->hasCast || $fA >= $nA) {
				$comA = $attacker[++$cA] ?? null;
				$nA   = $comA?->Size();
				$hits = $comA?->Weapon()->Hits();
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

			if ($comA->fighter($fA)->health > 0) {
				$damage += $comD->assault($fD, $comA, $fA);
			}
			if (++$hit >= $hits) {
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
				$size                  = $combatant->Size();
				$combatant->distracted = 0;
				$combatant->hasCast    = false;
				foreach ($combatant->fighters as $f => $fighter) {
					if ($fighter->health <= 0) {
						if ($fighter->potion instanceof HealingPotion) {
							$combatant->fighters[$f]->heal();
							Lemuria::Log()->debug('Fighter ' . $combatant->getId($f) . ' is saved from a deadly strike by a healing potion.');
							BattleLog::getInstance()->add(new FighterSavedMessage($combatant->getId($f)));
						} else {
							$id = $combatant->getId($f);
							Lemuria::Log()->debug('Fighter ' . $id . ' is dead.');
							BattleLog::getInstance()->add(new FighterIsDeadMessage($id));
							$combatant->hasDied($f);
						}
					}
				}
				$deceased = $size - $combatant->Size();
				if ($deceased > 0) {
					$unit = $combatant->Unit();
					$unit->setSize($unit->Size() - $deceased);
					$army   = $combatant->Army();
					$race   = $unit->Race();
					$trophy = $race instanceof Monster ? $race->Trophy() : null;
					if ($trophy) {
						$army->Trophies()->add(new Quantity($trophy, $deceased));
					}
					$inventory = $unit->Inventory();
					foreach ($combatant->Distribution()->lose($deceased) as $quantity /* @var Quantity $quantity*/) {
						$inventory->remove($quantity);
						$army->Loss()->add(new Quantity($quantity->Commodity(), $quantity->Count()));
						// Lemuria::Log()->debug('Unit ' . $unit . ' loses ' . $quantity . '.');
					}
					if ($combatant->Size() <= 0) {
						unset($combatants[$c]);
						$combatants = array_values($combatants);
						$unit->setIsGuarding(false)->setIsHiding(false);
						// Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' was wiped out.');
					} else {
						//$combatant->fighters = array_values($combatant->fighters);
						// Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' has ' . $deceased . ' losses.');
					}
				}
			}
		}
	}
}
