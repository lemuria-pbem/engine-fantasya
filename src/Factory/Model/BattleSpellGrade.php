<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Engine\Fantasya\Combat\Combat;
use Lemuria\Model\Fantasya\BattleSpell;
use Lemuria\Model\Fantasya\SpellGrade;

class BattleSpellGrade
{
	protected float $reliability = 1.0;

	public function __construct(protected readonly SpellGrade $spellGrade, protected readonly Combat $combat) {
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

	public function setReliability(float $reliability): static {
		$this->reliability = $reliability;
		return $this;
	}
}
