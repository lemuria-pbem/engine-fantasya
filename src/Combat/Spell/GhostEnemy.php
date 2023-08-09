<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\CombatEffect;
use Lemuria\Model\Fantasya\Talent\Magic;

class GhostEnemy extends AbstractBattleSpell
{
	public function cast(): int {
		$grade = parent::cast();
		if ($grade > 0) {
			$magic    = $this->calculus->knowledge(Magic::class)->Level();
			$spell    = $this->Spell();
			$effect   = new CombatEffect($spell, $grade);
			$duration = $magic + 2 * ($magic - $spell->Difficulty());
			$this->caster->Effects()->add($effect->setDuration($duration));
		}
		return $grade;
	}
}
