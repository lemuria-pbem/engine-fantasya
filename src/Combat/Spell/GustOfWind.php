<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\CombatEffect;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

class GustOfWind extends AbstractBattleSpell
{
	public function cast(Unit $unit): int {
		$grade = parent::cast($unit);
		if ($grade > 0) {
			$spell    = $this->grade->Spell();
			$calculus = new Calculus($unit);
			$level    = $calculus->knowledge(Magic::class)->Level();
			$this->grade->Combat()->Effects()->add(new CombatEffect($spell, $level));
			Lemuria::Log()->debug('A sharp gust of wind blows over the battlefield.');
		}
		return $grade;
	}
}
