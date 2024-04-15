<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Engine\Fantasya\Combat\Feature;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Talent\Magic;

class ShockWave extends AbstractBattleSpell
{
	protected const int VICTIMS = 5;

	public function cast(): int {
		$grade = parent::cast();
		if ($grade > 0) {
			$level    = $this->calculus->knowledge(Magic::class)->Level();
			$fighters = (int)floor($grade * self::VICTIMS * sqrt($level));
			$fighters = $this->featureFighters($this->victim[BattleRow::Front->value], $fighters, Feature::ShockWave);
			$this->featureFighters($this->victim[BattleRow::Back->value], $fighters, Feature::ShockWave);
		}
		return $grade;
	}

	protected function featureFightersMessage(Combatant $combatant, int $count): void {
		Lemuria::Log()->debug($count . ' fighters of combatant ' . $combatant->Id() . ' are distracted by a Shockwave.');
	}
}
