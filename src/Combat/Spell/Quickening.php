<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\Combatant;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

class Quickening extends AbstractBattleSpell
{
	protected const DURATION = 5;

	protected const FIGHTERS = 10;

	protected int $duration;

	protected bool $addQuickening;

	public function cast(Unit $unit): int {
		$grade = parent::cast($unit);
		if ($grade > 0) {
			$fighters       = $grade * self::FIGHTERS;
			$calculus       = new Calculus($unit);
			$level          = $calculus->knowledge(Magic::class)->Level();
			$this->duration = self::DURATION + $level - $this->grade->Spell()->Difficulty();

			// First iteration: Add Quickening to fighters that have no Quickening yet.
			$this->addQuickening = false;
			$fighters = $this->quickenFighters($this->victim[BattleRow::FRONT->value], $fighters);
			$fighters = $this->quickenFighters($this->victim[BattleRow::BACK->value], $fighters);
			$fighters = $this->quickenFighters($this->victim[BattleRow::BYSTANDER->value], $fighters);
			$fighters = $this->quickenFighters($this->victim[BattleRow::REFUGEE->value], $fighters);

			// Second iteration: Increase Quickening for fighters that have less.
			if ($fighters > 0) {
				$this->addQuickening = true;
				$fighters = $this->quickenFighters($this->victim[BattleRow::FRONT->value], $fighters);
				$fighters = $this->quickenFighters($this->victim[BattleRow::BACK->value], $fighters);
				$fighters = $this->quickenFighters($this->victim[BattleRow::BYSTANDER->value], $fighters);
				$this->quickenFighters($this->victim[BattleRow::REFUGEE->value], $fighters);
			}
		}
		return $grade;
	}

	/**
	 * @param Combatant[] $combatants
	 */
	protected function quickenFighters(array $combatants, int $fighters): int {
		foreach (array_keys($combatants) as $i) {
			if ($fighters <= 0) {
				break;
			}
			$combatant = $combatants[$i];
			$size      = $combatant->Size();
			$next      = 0;
			$count     = 0;
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
