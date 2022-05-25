<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Feature;
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
			$fighters = $grade * self::VICTIMS * sqrt($level);
			$fighters = $this->featureFighters($this->caster[BattleRow::FRONT->value], $fighters, Feature::Shockwave);
			$this->featureFighters($this->caster[BattleRow::BACK->value], $fighters, Feature::Shockwave);
		}
		return $grade;
	}

	protected function featureFightersMessage(Combatant $combatant, int $count): void {
		Lemuria::Log()->debug($count . ' fighters of combatant ' . $combatant->Id() . ' are distracted by a Shockwave.');
	}
}
