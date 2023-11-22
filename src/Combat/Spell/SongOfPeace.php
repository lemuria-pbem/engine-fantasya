<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Message\SongOfPeaceCombatantMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\SongOfPeaceFighterMessage;
use Lemuria\Engine\Fantasya\Combat\Rank;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Talent\Magic;

class SongOfPeace extends AbstractBattleSpell
{
	protected const int POINTS = 10;

	public function cast(): int {
		$grade = parent::cast();
		if ($grade > 0) {
			$level       = $this->calculus->knowledge(Magic::class)->Level();
			$gradePoints = $grade * self::POINTS * $level;
			$gradePoints = $this->castOnCombatants($this->victim[BattleRow::Front->value], $gradePoints);
			$gradePoints = $this->castOnCombatants($this->victim[BattleRow::Back->value], $gradePoints);
			$gradePoints = $this->castOnCombatants($this->victim[BattleRow::Bystander->value], $gradePoints);
			$this->castOnCombatants($this->victim[BattleRow::Refugee->value], $gradePoints);
		}
		return $grade;
	}

	/**
	 * @param array<Combatant> $combatants
	 */
	protected function castOnCombatants(Rank $combatants, int $gradePoints): int {
		foreach ($combatants as $i => $combatant) {
			if ($gradePoints <= 0) {
				break;
			}
			$size = $combatant->Size();
			if ($size > $gradePoints) {
				array_splice($combatant->fighters, -$gradePoints, $gradePoints);
				Lemuria::Log()->debug($gradePoints . ' fighters of combatant ' . $combatant->Id() . ' leave the battlefield in peace.');
				BattleLog::getInstance()->add(new SongOfPeaceFighterMessage($combatant, $gradePoints));
				$gradePoints = 0;
			} else {
				unset($combatants[$i]);
				$gradePoints -= $size;
				Lemuria::Log()->debug('Combatant ' . $combatant->Id() . ' leaves the battlefield in peace.');
				BattleLog::getInstance()->add(new SongOfPeaceCombatantMessage($combatant));
			}
		}
		return $gradePoints;
	}
}
