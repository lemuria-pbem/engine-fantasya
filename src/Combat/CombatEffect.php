<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use Lemuria\Engine\Fantasya\Combat\Spell\AbstractBattleSpell;
use Lemuria\Item;
use Lemuria\Model\Fantasya\BattleSpell;

class CombatEffect extends Item
{
	private AbstractBattleSpell $combatSpell;

	private int $duration = PHP_INT_MAX;

	public function __construct(BattleSpell $spell, int $points) {
		parent::__construct($spell, $points);
	}

	public function Spell(): BattleSpell {
		/** @var BattleSpell $spell */
		$spell = $this->getObject();
		return $spell;
	}

	public function Points(): int {
		return $this->Count();
	}

	public function Duration(): int {
		return $this->duration;
	}

	public function CombatSpell(): AbstractBattleSpell {
		return $this->combatSpell;
	}

	public function add(CombatEffect $effect): CombatEffect {
		$this->addItem($effect);
		$this->duration = max($this->duration, $effect->duration);

		return $this;
	}

	public function remove(CombatEffect $effect): CombatEffect {
		$this->removeItem($effect);

		return $this;
	}

	public function setCombatSpell(AbstractBattleSpell $combatSpell): CombatEffect {
		$this->combatSpell = $combatSpell;
		return $this;
	}

	public function setDuration(int $duration): CombatEffect {
		$this->duration = $duration;
		return $this;
	}
}
