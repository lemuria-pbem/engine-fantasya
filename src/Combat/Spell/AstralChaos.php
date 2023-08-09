<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\CombatEffect;
use Lemuria\Model\Fantasya\Talent\Magic;

class AstralChaos extends AbstractBattleSpell
{
	public function cast(): int {
		$grade = parent::cast();
		if ($grade > 0) {
			$spell  = $this->grade->Spell();
			$level  = $this->calculus->knowledge(Magic::class)->Level();
			$points = $grade * $level;
			$this->grade->Combat()->addEffect(new CombatEffect($spell, $points));
		}
		return $grade;
	}

	/**
	 * Astral Chaos will never fail.
	 */
	protected function modifyReliability(int $grade): int {
		return $grade;
	}
}
