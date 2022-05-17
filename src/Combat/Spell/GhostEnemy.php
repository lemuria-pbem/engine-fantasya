<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\CombatEffect;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

class GhostEnemy extends AbstractBattleSpell
{
	public function cast(Unit $unit): int {
		$grade = parent::cast($unit);
		if ($grade > 0) {
			$calculus = new Calculus($unit);
			$magic    = $calculus->knowledge(Magic::class)->Level();
			$spell    = $this->Spell();
			$effect   = new CombatEffect($spell, $grade);
			$duration = $magic + 2 * ($magic - $spell->Difficulty());
			$this->caster->Effects()->add($effect->setDuration($duration));
		}
		return $grade;
	}
}
