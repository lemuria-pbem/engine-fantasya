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

class GazeOfTheBasilisk extends AbstractBattleSpell
{
	public function cast(Unit $unit): int {
		$grade = parent::cast($unit);
		if ($grade > 0) {
			$calculus = new Calculus($unit);
			$level    = $calculus->knowledge(Magic::class)->Level();
			$fighters = $grade * $level;

			$fighters = $this->stoneFighters($this->victim[BattleRow::FRONT->value], $fighters);
			$fighters = $this->stoneFighters($this->victim[BattleRow::BACK->value], $fighters);
			$fighters = $this->stoneFighters($this->victim[BattleRow::BYSTANDER->value], $fighters);
			$this->stoneFighters($this->victim[BattleRow::REFUGEE->value], $fighters);
		}
		return $grade;
	}

	/**
	 * @noinspection DuplicatedCode
	 */
	protected function stoneFighters(Rank $combatants, int $fighters): int {
		foreach ($combatants as $combatant) {
			if ($fighters <= 0) {
				break;
			}
			$size  = $combatant->Size();
			$next  = 0;
			$count = 0;
			while ($fighters > 0 && $next < $size) {
				$fighter = $combatant->fighters[$next++];
				if ($fighter->hasFeature(Feature::GazeOfTheBasilisk)) {
					continue;
				}
				$fighter->setFeature(Feature::GazeOfTheBasilisk);
				$fighters--;
				$count++;
			}
			Lemuria::Log()->debug($count . ' fighters of combatant ' . $combatant->Id() . ' receive GazeOfTheBasilisk.');
		}
		return $fighters;
	}
}
