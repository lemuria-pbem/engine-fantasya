<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Feature;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Talent\Magic;

class Shockwave extends AbstractBattleSpell
{
	protected const int VICTIMS = 5;

	public function cast(): int {
		$grade = parent::cast();
		if ($grade > 0) {
			$level    = $this->calculus->knowledge(Magic::class)->Level();
			$fighters = $grade * self::VICTIMS * sqrt($level);
			$fighters = $this->featureFighters($this->caster[BattleRow::Front->value], $fighters, Feature::Shockwave);
			$this->featureFighters($this->caster[BattleRow::Back->value], $fighters, Feature::Shockwave);
		}
		return $grade;
	}

	protected function featureFightersMessage(Combatant $combatant, int $count): void {
		Lemuria::Log()->debug($count . ' fighters of combatant ' . $combatant->Id() . ' are distracted by a Shockwave.');
	}
}
