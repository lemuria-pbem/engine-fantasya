<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleSpellCastMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleSpellFailedMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleSpellNoAuraMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\SongOfPeaceCombatantMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

class SongOfPeace extends AbstractBattleSpell
{
	protected const int POINTS_PER_LEVEL = 10;

	public function cast(): int {
		$unit         = $this->calculus->Unit();
		$initialGrade = $this->grade($unit);
		$grade        = $this->modifyReliability($initialGrade);
		$grade        = $this->getAvailableGrade($unit, $grade);
		$this->consume($unit, $grade);
		if ($grade > 0) {
			if ($grade < $initialGrade) {
				BattleLog::getInstance()->add(new BattleSpellFailedMessage($unit, $this->grade->Spell()));
			} else {
				Lemuria::Log()->debug('Unit ' . $unit . ' casts ' . $this->grade->Spell() . ' with grade ' . $grade . '.');
				BattleLog::getInstance()->add(new BattleSpellCastMessage($unit, $this->grade->Spell(), $grade));
				foreach ($this->victim as $combatants) {
					foreach ($combatants as $combatant) {
						Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' leaves the battlefield in peace.');
						BattleLog::getInstance()->add(new SongOfPeaceCombatantMessage($combatant));
					}
				}
			}

		} else {
			BattleLog::getInstance()->add(new BattleSpellNoAuraMessage($unit, $this->grade->Spell()));
		}
		return $grade;
	}

	protected function grade(Unit $unit): int {
		$persons = $this->countVictimsCombatants();
		$level   = $this->calculus->knowledge(Magic::class)->Level();
		$rate    = $level * self::POINTS_PER_LEVEL;
		return (int)ceil($persons / $rate);
	}

	protected function consume(Unit $unit, int $grade): void {
		$aura        = $unit->Aura();
		$available   = $aura->Aura();
		$consumption = $grade * $this->grade->Spell()->Aura();
		$aura->setAura($available - $consumption);
	}

	private function countVictimsCombatants(): int {
		$count = 0;
		foreach ($this->victim as $combatants) {
			foreach ($combatants as $combatant) {
				$count += $combatant->Size();
			}
		}
		return $count;
	}

	private function getAvailableGrade(Unit $unit, int $grade): int {
		$aura        = $unit->Aura()->Aura();
		$available   = (int)floor($aura / $this->grade->Spell()->Aura());
		return min($available, $grade);
	}
}
