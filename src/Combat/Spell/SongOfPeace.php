<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Message\SongOfPeaceCombatantMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\SongOfPeaceFighterMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

class SongOfPeace extends AbstractBattleSpell
{
	protected const POINTS = 10;

	public function cast(Unit $unit): int {
		$grade = parent::cast($unit);
		if ($grade > 0) {
			$calculus    = new Calculus($unit);
			$level       = $calculus->knowledge(Magic::class)->Level();
			$gradePoints = $grade * self::POINTS * $level;
			$gradePoints = $this->castOnCombatants($this->victim[BattleRow::FRONT->value], $gradePoints);
			$gradePoints = $this->castOnCombatants($this->victim[BattleRow::BACK->value], $gradePoints);
			$gradePoints = $this->castOnCombatants($this->victim[BattleRow::BYSTANDER->value], $gradePoints);
			$this->castOnCombatants($this->victim[BattleRow::REFUGEE->value], $gradePoints);
		}
		return $grade;
	}

	/**
	 * @param Combatant[] $combatants
	 */
	protected function castOnCombatants(array &$combatants, int $gradePoints): int {
		foreach (array_keys($combatants) as $i) {
			if ($gradePoints <= 0) {
				break;
			}
			$combatant = $combatants[$i];
			$size      = $combatant->Size();
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
