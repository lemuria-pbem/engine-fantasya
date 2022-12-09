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

class GazeOfTheBasilisk extends AbstractBattleSpell
{
	public function cast(Unit $unit): int {
		$grade = parent::cast($unit);
		if ($grade > 0) {
			$calculus = new Calculus($unit);
			$level    = $calculus->knowledge(Magic::class)->Level();
			$fighters = $grade * $level;

			$fighters = $this->featureFighters($this->victim[BattleRow::Front->value], $fighters, Feature::GazeOfTheBasilisk);
			$fighters = $this->featureFighters($this->victim[BattleRow::Back->value], $fighters, Feature::GazeOfTheBasilisk);
			$fighters = $this->featureFighters($this->victim[BattleRow::Bystander->value], $fighters, Feature::GazeOfTheBasilisk);
			$this->featureFighters($this->victim[BattleRow::Refugee->value], $fighters, Feature::GazeOfTheBasilisk);
		}
		return $grade;
	}

	protected function featureFightersMessage(Combatant $combatant, int $count): void {
		Lemuria::Log()->debug($count . ' fighters of combatant ' . $combatant->Id() . ' receive GazeOfTheBasilisk.');
	}
}
