<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

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
use Lemuria\Engine\Fantasya\Combat\Log\Message\TacticsRoundMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\TriedToFleeFromBattleMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Participant;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\Model\BattleSpellGrade;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Commodity\Potion\HealingPotion;
use Lemuria\Model\Fantasya\Commodity\Weapon\Native;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Monster;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

class Combat
{
	use BuilderTrait;

	public final const ROW_NAME = [Rank::REFUGEE => 'refugees', Rank::BYSTANDER => 'bystanders', Rank::BACK => 'back', Rank::FRONT => 'front'];

	protected const OVERRUN = 3.0;

	protected int $round = 0;

	protected Ranks $attacker;

	protected Ranks $defender;

	/**
	 * @var array<int, bool>
	 */
	protected array $isAttacker = [];

	/**
	 * @var array<int, Army>
	 */
	protected array $armies = [];

	/**
	 * @var array<Participant>
	 */
	protected array $attackParticipants = [];

	/**
	 * @var array<Participant>
	 */
	protected array $defendParticipants = [];

	/**
	 * Effects that impact both sides.
	 */
	protected Effects $effects;

	/**
	 * @var array(bool, int)
	 */
	protected array $lastReinforcements = [false => PHP_INT_MAX, true => PHP_INT_MAX];

	protected int $newZombies = 0;

	public static function getBattleRow(Unit $unit): BattleRow {
		$battleRow = $unit->BattleRow();
		return match ($battleRow) {
			BattleRow::Defensive                      => BattleRow::Back,
			BattleRow::Careful, BattleRow::Aggressive => BattleRow::Front,
			default                                   => $battleRow
		};
	}

	public function __construct(private Context $context) {
		$this->attacker = new Ranks(true);
		$this->defender = new Ranks(false);
		$this->effects  = new Effects();
	}

	public function hasAttackers(): bool {
		return $this->attacker->count() > 0;
	}

	public function hasDefenders(): bool {
		return $this->defender->count() > 0;
	}

	public function addAttacker(Unit $unit): Army {
		$army                  = $this->getArmy($unit)->add($unit);
		$id                    = $army->Party()->Id()->Id();
		$this->isAttacker[$id] = true;
		return $army;
	}

	public function addDefender(Unit $unit): Army {
		$army                  = $this->getArmy($unit)->add($unit);
		$id                    = $army->Party()->Id()->Id();
		$this->isAttacker[$id] = false;
		return $army;
	}

	/**
	 * @return array<int, Army>
	 */
	public function getAttackers(): array {
		$armies = [];
		foreach ($this->isAttacker as $id => $isAttacker) {
			if ($isAttacker) {
				$army = $this->armies[$id];
				foreach ($army->Units() as $unit) {
					$combatants = $army->getCombatants($unit);
					foreach ($combatants as $combatant) {
						$this->attacker->add($combatant);
					}
					$this->attackParticipants[] = new Participant(new Entity($unit), $army->getCombatants($unit));
				}
				$armies[$army->Id()] = $army;
			}
		}
		return $armies;
	}

	/**
	 * @return array<int, Army>
	 */
	public function getDefenders(): array {
		$armies = [];
		foreach ($this->isAttacker as $id => $isAttacker) {
			if (!$isAttacker) {
				$army = $this->armies[$id];
				foreach ($army->Units() as $unit) {
					$combatants = $army->getCombatants($unit);
					foreach ($combatants as $combatant) {
						$this->defender->add($combatant);
					}
					$this->defendParticipants[] = new Participant(new Entity($unit), $army->getCombatants($unit));
				}
				$armies[$army->Id()] = $army;
			}
		}
		return $armies;
	}

	public function embattle(): Combat {
		$log = BattleLog::getInstance();
		$log->add(new AttackerSideMessage($this->attackParticipants));
		foreach ($this->attacker as $rank) {
			foreach ($rank as $combatant) {
				$log->add($this->getCombatantMessage($combatant));
			}
		}
		$log->add(new DefenderSideMessage($this->defendParticipants));
		foreach ($this->defender as $rank) {
			foreach ($rank as $combatant) {
				$log->add($this->getCombatantMessage($combatant));
			}
		}
		return $this;
	}

	public function castPreparationSpells(?Party $first): Combat {
		$casts = new Casts();
		if ($first) {
			if ($this->isAttacker[$first->Id()->Id()]) {
				$this->addPreparationSpells($casts, $this->attacker, $this->defender);
				$casts->cast();
				$this->addPreparationSpells($casts->clear(), $this->defender, $this->attacker);
			} else {
				$this->addPreparationSpells($casts, $this->defender, $this->attacker);
				$casts->cast();
				$this->addPreparationSpells($casts->clear(), $this->attacker, $this->defender);
			}
		} else {
			$this->addPreparationSpells($casts, $this->attacker, $this->defender);
			$this->addPreparationSpells($casts, $this->defender, $this->attacker);
		}
		$casts->cast();
		return $this;
	}

	public function tacticsRound(Party $party): Combat {
		BattleLog::getInstance()->add(new TacticsRoundMessage());
		if ($this->isAttacker[$party->Id()->Id()]) {
			BattleLog::getInstance()->add(new AttackerTacticsRoundMessage());
		} else {
			BattleLog::getInstance()->add(new DefenderTacticsRoundMessage());
		}

		$this->everybodyTryToFlee();
		if ($this->arrangeBattleRows()) {
			$this->fleeFromBattle($this->attacker[Rank::REFUGEE], 'Attacker', true);
			$this->fleeFromBattle($this->defender[Rank::REFUGEE], 'Defender', true);
			if ($this->isAttacker[$party->Id()->Id()]) {
				Lemuria::Log()->debug('Attacker gets first strike in tactics round.');
				$this->attack($this->attacker, $this->defender, 'Attacker');
			} else {
				Lemuria::Log()->debug('Defender gets first strike in tactics round.');
				$this->attack($this->defender, $this->attacker, 'Defender');
			}
		} else {
			BattleLog::getInstance()->add(new EveryoneHasFledMessage());
			Lemuria::Log()->debug('Everyone has fled before the battle could begin.');
		}

		return $this;
	}

	public function nextRound(): int {
		$this->unsetExpiredCombatSpells();
		$this->unsetFighterFeatures();
		$this->everybodyTryToFlee();
		if ($this->arrangeBattleRows()) {
			$this->fleeFromBattle($this->attacker[Rank::REFUGEE], 'Attacker', true);
			$this->fleeFromBattle($this->defender[Rank::REFUGEE], 'Defender', true);
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

	public function getRounds(): int {
		return $this->round;
	}

	public function getArmy(Unit $unit): Army {
		$party = $unit->Party();
		$id    = $party->Id()->Id();
		if (!isset($this->armies[$id])) {
			$this->armies[$id] = new Army($party, $this);
		}
		return $this->armies[$id];
	}

	public function getEffect(BattleSpell $spell, ?Combatant $combatant = null): ?CombatEffect {
		if ($combatant) {
			$id      = $combatant->Unit()->Party()->Id()->Id();
			$effects = isset($this->isAttacker[$id]) ? $this->attacker->Effects() : $this->defender->Effects();
			if ($effects->offsetExists($spell)) {
				return $effects[$spell];
			}
		}

		/** @var CombatEffect $effect */
		$effect = $this->effects[$spell] ?? null;
		return $effect;
	}

	public function addEffect(CombatEffect $effect): Combat {
		$this->effects->add($effect);
		return $this;
	}

	public function getNewZombies(): int {
		return $this->newZombies;
	}

	protected function addPreparationSpells(Casts $casts, Ranks $caster, Ranks $victim): void {
		foreach ([Rank::FRONT, Rank::BACK] as $row) {
			foreach ($caster[$row] as $combatant) {
				$unit = $combatant->Unit();
				if ($unit->BattleSpells()?->Preparation()) {
					$grade = new BattleSpellGrade($unit->BattleSpells()->Preparation(), $this);
					//TODO Refactoring: Get rid of $grade->setUnit().
					$spell = $this->context->Factory()->castBattleSpell($grade->setUnit($unit));
					$casts->add($spell->setCaster($caster)->setVictim($victim));
				}
			}
		}
	}

	protected function setHasCast(Unit $unit, Rank $rank): void {
		foreach ($rank as $combatant) {
			if ($combatant->Unit() === $unit) {
				$combatant->hasCast = true;
			}
		}
	}

	protected function getCombatantMessage(Combatant $combatant): Message {
		if ($combatant->Weapon() instanceof Native) {
			return new CombatantNoWeaponMessage($combatant);
		}
		return new CombatantWeaponMessage($combatant);
	}

	protected function logSideDistribution(): void {
		$attacker = [];
		foreach ($this->attacker as $row => $combatants) {
			$count = 0;
			foreach ($combatants as $combatant) {
				$count += $combatant->Size();
			}
			$attacker[] = self::ROW_NAME[$row] . ':' . $count;
		}
		$defender = [];
		foreach ($this->defender as $row => $combatants) {
			$count = 0;
			foreach ($combatants as $combatant) {
				$count += $combatant->Size();
			}
			$defender[] = self::ROW_NAME[$row] . ':' . $count;
		}
		Lemuria::Log()->debug('Combatant distribution is ' . implode(' | ', $attacker) . ' vs. ' . implode(' | ', $defender) . '.');
	}

	protected function arrangeBattleRows(): bool {
		$attackers = $this->attacker[Rank::FRONT]->Size();
		$defenders = $this->defender[Rank::FRONT]->Size();
		if ($attackers > 0 || $defenders > 0) {
			$ratio = $defenders > 0 ? $attackers / $defenders : PHP_INT_MAX;
			if ($ratio > self::OVERRUN) {
				$additional = (int)ceil($attackers / self::OVERRUN) - $defenders;
				$this->arrangeRows($additional, $this->defender, false);
			} elseif ($ratio < 1.0 / self::OVERRUN) {
				$additional = (int)ceil($defenders / self::OVERRUN) - $attackers;
				$this->arrangeRows($additional, $this->attacker, true);
			} else {
				return true;
			}
		} else {
			$this->arrangeRows(1, $this->attacker, true);
			$this->arrangeRows(1, $this->defender, false);
		}
		$this->logSideDistribution();
		return $this->hasAttackers() && $this->hasDefenders();
	}

	protected function arrangeRows(int $additional, Ranks $side, $isAttacker): void {
		$needed = $additional;
		if ($additional > 0) {
			$additional = $this->arrangeFromRow($side, $isAttacker, Rank::BACK, $additional);
		}
		if ($additional > 0) {
			$additional = $this->arrangeFromRow($side, $isAttacker, Rank::BYSTANDER, $additional);
		}
		if ($additional > 0) {
			$additional = $this->arrangeFromRow($side, $isAttacker, Rank::REFUGEE, $additional);
		}

		$reinforcements                        = $needed - $additional;
		$lastReinforcements                    = $this->lastReinforcements[$isAttacker];
		$this->lastReinforcements[$isAttacker] = $reinforcements;
		if ($reinforcements <= 0 && $lastReinforcements > 0) {
			if ($isAttacker) {
				BattleLog::getInstance()->add(new AttackerOverrunMessage($needed));
			} else {
				BattleLog::getInstance()->add(new DefenderOverrunMessage($needed));
			}
			if ($additional > 0) {
				if ($isAttacker) {
					BattleLog::getInstance()->add(new AttackerNoReinforcementMessage());
				} else {
					BattleLog::getInstance()->add(new DefenderNoReinforcementMessage());
				}
			}
		}
	}

	protected function arrangeFromRow(Ranks $side, bool $isAttacker, int $battleRow, int $additional): int {
		$rank = $side[$battleRow];
		if ($rank->count() <= 0) {
			return $additional;
		}

		$name  = self::ROW_NAME[$battleRow];
		$front = $side[Rank::FRONT];
		foreach ($rank as $i => $combatant) {
			$unit = $combatant->Unit();
			$size = $combatant->Size();
			if ($size <= $additional) {
				$front->add($combatant->setBattleRow(BattleRow::Front));
				unset($rank[$i]);
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
				$front->add($newCombatant);
				$who       = $isAttacker ? 'Attacker' : 'Defender';
				$hasWeapon = !($newCombatant->Weapon() instanceof Native);
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
		$this->fleeIfWounded($this->attacker[Rank::FRONT], 'Attacker');
		$this->fleeIfWounded($this->attacker[Rank::BACK], 'Attacker');
		$this->fleeIfWounded($this->attacker[Rank::BYSTANDER], 'Attacker');
		$this->fleeFromBattle($this->attacker[Rank::REFUGEE], 'Attacker');
		$this->fleeIfWounded($this->defender[Rank::FRONT], 'Defender');
		$this->fleeIfWounded($this->defender[Rank::BACK], 'Defender');
		$this->fleeIfWounded($this->defender[Rank::BYSTANDER], 'Defender');
		$this->fleeFromBattle($this->defender[Rank::REFUGEE], 'Defender');
	}

	protected function fleeIfWounded(Rank $rank, string $who): void {
		foreach ($rank as $i => $combatant) {
			foreach ($combatant->fighters as $f => $fighter) {
				$chance = $combatant->isFleeing($fighter);
				if (is_float($chance)) {
					if ($chance >= 0.0) {
						$combatant->flee($f);
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
				unset($rank[$i]);
				// Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' has fled from battle.');
			}
		}
	}

	protected function fleeFromBattle(Rank $rank, string $who, $fleeSuccessfully = false): void {
		foreach ($rank as $i => $combatant) {
			if ($fleeSuccessfully) {
				unset($rank[$i]);
				Lemuria::Log()->debug($who . ' combatant ' . $combatant->Id() . ' flees from battle.');
				BattleLog::getInstance()->add(new FleeFromBattleMessage($combatant));
			} else {
				$chance = $combatant->canFlee();
				if ($chance >= 0.0) {
					unset($rank[$i]);
					Lemuria::Log()->debug($who . ' combatant ' . $combatant->Id() . ' managed to flee from battle (chance: ' . $chance . ').');
					BattleLog::getInstance()->add(new ManagedToFleeFromBattleMessage($combatant));
				} else {
					Lemuria::Log()->debug($who . ' combatant ' . $combatant->Id() . ' tried to flee from battle (chance: ' . -$chance . ').');
					BattleLog::getInstance()->add(new TriedToFleeFromBattleMessage($combatant));
				}
			}
		}
	}

	protected function unsetExpiredCombatSpells(?Effects $effects = null): void {
		if (!$effects) {
			$this->unsetExpiredCombatSpells($this->effects);
			$this->unsetExpiredCombatSpells($this->attacker->Effects());
			$this->unsetExpiredCombatSpells($this->defender->Effects());
		} else {
			$removal = [];
			foreach ($effects as $effect /** @var CombatEffect $effect */) {
				$duration = $effect->Duration() - 1;
				if ($duration > 0) {
					$effect->setDuration($duration);
				} else {
					$removal[] = $effect->Spell();
				}
			}
			foreach ($removal as $spell) {
				$effects->offsetUnset($spell);
				Lemuria::Log()->debug('Battle spell ' . $spell . ' has expired.');
			}
		}
	}

	protected function unsetFighterFeatures(): void {
		foreach ($this->attacker as $rank) {
			foreach ($rank as $combatant) {
				$combatant->unsetFeatures([Feature::Shockwave]);
				$combatant->unsetFeatures([Feature::GazeOfTheBasilisk]);
			}
		}
		foreach ($this->defender as $rank) {
			foreach ($rank as $combatant) {
				$combatant->unsetFeatures([Feature::Shockwave]);
				$combatant->unsetFeatures([Feature::GazeOfTheBasilisk]);
			}
		}
	}

	protected function castCombatSpells(): void {
		$attacker = $this->prepareCombatSpells($this->attacker);
		$defender = $this->prepareCombatSpells($this->defender);
		$this->castCombatSpell($attacker, $this->attacker, $this->defender);
		$this->castCombatSpell($defender, $this->defender, $this->attacker);
	}

	/**
	 * @return array<Unit>
	 */
	protected function prepareCombatSpells(Ranks $caster): array {
		$units = [];
		foreach ($caster[Rank::FRONT] as $combatant) {
			$unit = $combatant->Unit();
			if ($unit->BattleSpells()?->Combat()) {
				$units[$unit->Id()->Id()] = $unit;
			}
		}
		foreach ($caster[Rank::BACK] as $combatant) {
			$unit = $combatant->Unit();
			if ($unit->BattleSpells()?->Combat()) {
				$units[$unit->Id()->Id()] = $unit;
			}
		}
		return array_values($units);
	}

	/**
	 * @param array<Unit> $units
	 */
	protected function castCombatSpell(array $units, Ranks $caster, Ranks $victim): void {
		foreach ($units as $unit) {
			$grade = new BattleSpellGrade($unit->BattleSpells()->Combat(), $this);
			//TODO Refactoring: Get rid of $grade->setUnit().
			$spell = $this->context->Factory()->castBattleSpell($grade->setUnit($unit));
			$grade = $spell->setCaster($caster)->setVictim($victim)->cast($unit);
			if ($grade > 0) {
				$this->setHasCast($unit, $caster[Rank::FRONT]);
				$this->setHasCast($unit, $caster[Rank::BACK]);
			}
		}
	}

	protected function attack(Ranks $attacker, Ranks $defender, string $who): int {

		$message = $who . ': Front row attacks.';
		$damage  = $this->attackRowAgainstRow($attacker[Rank::FRONT], $defender[Rank::FRONT], $message);
		$message = $who . ': Back row attacks.';
		$damage += $this->attackRowAgainstRow($attacker[Rank::BACK], $defender[Rank::FRONT], $message);
		$this->removeTheDead($attacker);
		$this->removeTheDead($defender);
		return $damage;
	}

	/**
	 * @noinspection PhpUnusedParameterInspection
	 */
	protected function attackRowAgainstRow(Rank $attacker, Rank $defender, string $message): int {
		$a  = count($attacker);
		$a1 = $attacker->Hits();
		if ($a <= 0 || $a1 <= 0) {
			return 0;
		}
		// Lemuria::Log()->debug($message);

		$charge = new Charge();
		$d      = count($defender);
		$d1     = $defender->Size();
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
		while ($cA < $a && $cD < $d) {
			/** @var Combatant $comA */
			if ($comA?->hasCast || $fA >= $nA) {
				$comA = $attacker[++$cA] ?? null;
				$nA   = $comA?->Size();
				$hits = $comA?->Weapon()->Hits();
				$fA   = 0;
				$hit  = 0;
				$charge->setAttacking($comA)->setAttacker($fA);
				continue;
			}
			if ($fD >= $nD) {
				$sum += $nD;
				/** @var Combatant $comD */
				$comD = $defender[++$cD] ?? null;
				$nD   = $comD?->Size();
				$last = $sum + $nD;
				$fD   = $nextD - $sum;
				$charge->setDefending($comD)->setDefender($fD);
				continue;
			}
			if ($nextD >= $last) {
				$fD = $nD;
				$charge->setDefender($fD);
				continue;
			}

			if ($comA->fighter($fA)->health > 0) {
				$charge->assault();
			}
			if (++$hit >= $hits) {
				$charge->setAttacker(++$fA);
				$hit = 0;
			}
			$fDStep = $nextD;
			$nextD  = (int)floor(++$nextA * $rate);
			$fDStep = $nextD - $fDStep;
			$fD    += $fDStep;
			$charge->setDefender($fD);
		}

		$attacker->addRound();
		return $charge->Damage();
	}

	/**
	 * @noinspection PhpStatementHasEmptyBodyInspection
	 */
	protected function removeTheDead(Ranks $ranks): void {
		foreach ($ranks as $combatants) {
			foreach ($combatants as $c => $combatant) {
				$size               = $combatant->Size();
				$combatant->hasCast = false;
				$createZombies      = $combatant->Unit()->Party()->Type() !== Type::Monster;
				foreach ($combatant->fighters as $f => $fighter) {
					if ($fighter->health <= 0) {
						if ($fighter->potion instanceof HealingPotion) {
							$fighter->heal();
							Lemuria::Log()->debug('Fighter ' . $combatant->getId($f) . ' is saved from a deadly strike by a healing potion.');
							BattleLog::getInstance()->add(new FighterSavedMessage($combatant->getId($f)));
						} else {
							$id = $combatant->getId($f);
							Lemuria::Log()->debug('Fighter ' . $id . ' is dead.');
							BattleLog::getInstance()->add(new FighterIsDeadMessage($id));
							$combatant->hasDied($f);
							if ($createZombies && $fighter->hasFeature(Feature::ZombieInfection)) {
								$this->newZombies++;
							}
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
					foreach ($combatant->Distribution()->lose($deceased) as $quantity) {
						$inventory->remove($quantity);
						$army->Loss()->add(new Quantity($quantity->Commodity(), $quantity->Count()));
						// Lemuria::Log()->debug('Unit ' . $unit . ' loses ' . $quantity . '.');
					}
					if ($combatant->Size() <= 0) {
						unset($combatants[$c]);
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
