<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\CombatEffect;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

class AstralChaos extends AbstractBattleSpell
{
	public function cast(Unit $unit): int {
		$grade = parent::cast($unit);
		if ($grade > 0) {
			$spell    = $this->grade->Spell();
			$calculus = new Calculus($unit);
			$level    = $calculus->knowledge(Magic::class)->Level();
			$points   = $grade * $level;
			$this->grade->Combat()->Effects()->add(new CombatEffect($spell, $points));
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
