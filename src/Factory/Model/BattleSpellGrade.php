<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Engine\Fantasya\Combat\Combat;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\SpellGrade;
use Lemuria\Model\Fantasya\Unit;

class BattleSpellGrade
{
	protected float $reliability = 1.0;

	/**
	 * @todo Refactoring: Check if this is needed anymore.
	 */
	private Unit $unit;

	public function __construct(protected readonly SpellGrade $spellGrade, protected readonly Combat $combat) {
	}

	/**
	 * @todo Refactoring: Check if this is needed anymore.
	 */
	public function Unit(): Unit {
		return $this->unit;
	}

	public function Spell(): BattleSpell {
		return $this->spellGrade->Spell();
	}

	public function Level(): int {
		return $this->spellGrade->Level();
	}

	public function Combat(): Combat {
		return $this->combat;
	}

	public function Reliability(): float {
		return $this->reliability;
	}

	public function setReliability(float $reliability): BattleSpellGrade {
		$this->reliability = $reliability;
		return $this;
	}

	/**
	 * @todo Refactoring: Check if this is needed anymore.
	 */
	public function setUnit(Unit $unit): BattleSpellGrade {
		$this->unit = $unit;
		return $this;
	}
}
