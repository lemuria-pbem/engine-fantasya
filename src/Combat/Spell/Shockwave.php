<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Log\Message\ShockwaveHitMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat;
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
			$victims  = $this->castOnCombatants($this->victim[Combat::FRONT], $victims);
			$this->castOnCombatants($this->victim[Combat::BACK], $victims);
		}
		return $grade;
	}

	/**
	 * @param Combatant[] $combatants
	 */
	protected function castOnCombatants(array &$combatants, int $victims): int {
		foreach (array_keys($combatants) as $i) {
			if ($victims <= 0) {
				break;
			}
			$combatant             = &$combatants[$i];
			$size                  = min($combatant->Size(), $victims);
			$combatant->distracted = $size;
			Lemuria::Log()->debug($size . ' fighters of combatant ' . $combatant->Id() . ' are distracted by a Shockwave.');
			BattleLog::getInstance()->add(new ShockwaveHitMessage($combatant->Id(), $size));
			$victims -= $size;
		}
		return $victims;
	}
}
