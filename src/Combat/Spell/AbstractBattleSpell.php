<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Spell;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Engine\Fantasya\Combat\BattleLog;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleSpellCastMessage;
use Lemuria\Engine\Fantasya\Combat\Log\Message\BattleSpellNoAuraMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\SpellGrade;
use Lemuria\Model\Fantasya\Unit;

abstract class AbstractBattleSpell
{
	protected array $caster;

	protected array $victim;

	protected Calculus $calculus;

	public function __construct(protected SpellGrade $grade) {
	}

	public function setCaster(array $combatantRows): AbstractBattleSpell {
		$this->caster = &$combatantRows;
		return $this;
	}

	public function setVictim(array &$combatantRows): AbstractBattleSpell {
		$this->victim = &$combatantRows;
		return $this;
	}

	public function cast(Unit $unit): int {
		$this->calculus = new Calculus($unit);
		$grade          = $this->grade($unit);
		if ($grade > 0) {
			$this->consume($unit, $grade);
			Lemuria::Log()->debug('Unit ' . $unit . ' casts ' . $this->grade->Spell() . ' with grade ' . $grade . '.');
			BattleLog::getInstance()->add(new BattleSpellCastMessage($unit, $this->grade->Spell(), $grade));
		} else {
			BattleLog::getInstance()->add(new BattleSpellNoAuraMessage($unit, $this->grade->Spell()));
		}
		return $grade;
	}

	protected function grade(Unit $unit): int {
		$aura      = $unit->Aura();
		$available = $aura->Aura();
		$maximum   = (int)floor($available / $this->grade->Spell()->Aura());
		return min($maximum, $this->grade->Level());
	}

	protected function consume(Unit $unit, int $grade): void {
		$aura      = $unit->Aura();
		$available = $aura->Aura();
		$aura->setAura($available - $grade * $this->grade->Spell()->Aura());
	}
}
