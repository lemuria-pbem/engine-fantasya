<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\Feature;
use Lemuria\Engine\Fantasya\Combat\Rank;
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

			$fighters = $this->armorFighters($this->victim[BattleRow::FRONT->value], $fighters);
			$fighters = $this->armorFighters($this->victim[BattleRow::BACK->value], $fighters);
			$fighters = $this->armorFighters($this->victim[BattleRow::BYSTANDER->value], $fighters);
			$this->armorFighters($this->victim[BattleRow::REFUGEE->value], $fighters);
		}
		return $grade;
	}

	protected function armorFighters(Rank $combatants, int $fighters): int {
		foreach ($combatants as $combatant) {
			if ($fighters <= 0) {
				break;
			}
			$size  = $combatant->Size();
			$next  = 0;
			$count = 0;
			while ($fighters > 0 && $next < $size) {
				$fighter = $combatant->fighters[$next++];
				if ($fighter->hasFeature(Feature::StoneSkin)) {
					continue;
				}
				$fighter->setFeature(Feature::StoneSkin);
				$fighters--;
				$count++;
			}
			Lemuria::Log()->debug($count . ' fighters of combatant ' . $combatant->Id() . ' receive StoneSkin.');
		}
		return $fighters;
	}
}
