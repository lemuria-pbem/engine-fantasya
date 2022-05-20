<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Log\Message\ShockwaveHitMessage;
use Lemuria\Engine\Fantasya\Combat\Rank;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

class Shockwave extends AbstractBattleSpell
{
	protected const VICTIMS = 5;

	public function cast(Unit $unit): int {
		$grade = parent::cast($unit);
		if ($grade > 0) {
			$calculus = new Calculus($unit);
			$level    = $calculus->knowledge(Magic::class)->Level();
			$victims  = $grade * self::VICTIMS * sqrt($level);
			$victims  = $this->castOnCombatants($this->victim[BattleRow::FRONT->value], $victims);
			$this->castOnCombatants($this->victim[BattleRow::BACK->value], $victims);
		}
		return $grade;
	}

	protected function castOnCombatants(Rank $combatants, int $victims): int {
		foreach ($combatants as $combatant) {
			if ($victims <= 0) {
				break;
			}
			$size                  = min($combatant->Size(), $victims);
			$combatant->distracted = $size;
			Lemuria::Log()->debug($size . ' fighters of combatant ' . $combatant->Id() . ' are distracted by a Shockwave.');
			BattleLog::getInstance()->add(new ShockwaveHitMessage($combatant->Id(), $size));
			$victims -= $size;
		}
		return $victims;
	}
}
