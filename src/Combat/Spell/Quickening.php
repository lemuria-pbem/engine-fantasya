<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Combat\Rank;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Talent\Magic;

class Quickening extends AbstractBattleSpell
{
	protected const int DURATION = 5;

	protected const int FIGHTERS = 10;

	protected int $duration;

	protected bool $addQuickening;

	public function cast(): int {
		$grade = parent::cast();
		if ($grade > 0) {
			$fighters       = $grade * self::FIGHTERS;
			$level          = $this->calculus->knowledge(Magic::class)->Level();
			$this->duration = self::DURATION + $level - $this->grade->Spell()->Difficulty();

			// First iteration: Add Quickening to fighters that have no Quickening yet.
			$this->addQuickening = false;
			$fighters = $this->quickenFighters($this->caster[BattleRow::Front->value], $fighters);
			$fighters = $this->quickenFighters($this->caster[BattleRow::Back->value], $fighters);
			$fighters = $this->quickenFighters($this->caster[BattleRow::Bystander->value], $fighters);
			$fighters = $this->quickenFighters($this->caster[BattleRow::Refugee->value], $fighters);

			// Second iteration: Increase Quickening for fighters that have less.
			if ($fighters > 0) {
				$this->addQuickening = true;
				$fighters = $this->quickenFighters($this->caster[BattleRow::Front->value], $fighters);
				$fighters = $this->quickenFighters($this->caster[BattleRow::Back->value], $fighters);
				$fighters = $this->quickenFighters($this->caster[BattleRow::Bystander->value], $fighters);
				$this->quickenFighters($this->caster[BattleRow::Refugee->value], $fighters);
			}
		}
		return $grade;
	}

	protected function quickenFighters(Rank $combatants, int $fighters): int {
		foreach ($combatants as $combatant) {
			if ($fighters <= 0) {
				break;
			}
			$size  = $combatant->Size();
			$next  = 0;
			$count = 0;
			while ($fighters > 0 && $next < $size) {
				$fighter = $combatant->fighters[$next++];
				if ($fighter->quickening > 0 && !$this->addQuickening) {
					continue;
				}
				$fighter->quickening = max($fighter->quickening, $this->duration);
				$fighters--;
				$count++;
			}
			Lemuria::Log()->debug($count . ' fighters of combatant ' . $combatant->Id() . ' receive Quickening ' . $this->duration . '.');
		}
		return $fighters;
	}
}
