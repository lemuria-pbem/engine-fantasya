<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\CombatEffect;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Talent\Magic;

class GustOfWind extends AbstractBattleSpell
{
	public function cast(): int {
		$grade = parent::cast();
		if ($grade > 0) {
			$spell  = $this->grade->Spell();
			$level  = $this->calculus->knowledge(Magic::class)->Level();
			$effect = new CombatEffect($spell, $level);
			$this->grade->Combat()->addEffect($effect->setDuration(1));
			Lemuria::Log()->debug('A sharp gust of wind blows over the battlefield.');
		}
		return $grade;
	}
}
