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

class StoneSkin extends AbstractBattleSpell
{
	public final const ATTACK_MALUS = 1;

	public final const BLOCK = 3;

	public function cast(Unit $unit): int {
		$grade = parent::cast($unit);
		if ($grade > 0) {
			$calculus = new Calculus($unit);
			$level    = $calculus->knowledge(Magic::class)->Level();
			$spell    = $this->grade->Spell()->Difficulty();
			$fighters = (int)floor($grade * $level / $spell);

			$fighters = $this->featureFighters($this->caster[BattleRow::Front->value], $fighters, Feature::StoneSkin);
			$fighters = $this->featureFighters($this->caster[BattleRow::Back->value], $fighters, Feature::StoneSkin);
			$fighters = $this->featureFighters($this->caster[BattleRow::Bystander->value], $fighters, Feature::StoneSkin);
			$this->featureFighters($this->caster[BattleRow::Refugee->value], $fighters, Feature::StoneSkin);
		}
		return $grade;
	}

	protected function featureFightersMessage(Combatant $combatant, int $count): void {
		Lemuria::Log()->debug($count . ' fighters of combatant ' . $combatant->Id() . ' receive StoneSkin.');
	}
}
