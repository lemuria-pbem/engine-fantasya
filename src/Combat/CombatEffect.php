<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Item;
use Lemuria\Model\Fantasya\BattleSpell;

class CombatEffect extends Item
{
	#[Pure] public function __construct(BattleSpell $spell, int $points) {
		parent::__construct($spell, $points);
	}

	#[Pure] public function Spell(): BattleSpell {
		/** @var BattleSpell $spell */
		$spell = $this->getObject();
		return $spell;
	}

	#[Pure] public function Points(): int {
		return $this->Count();
	}

	public function add(CombatEffect $effect): CombatEffect {
		$this->addItem($effect);

		return $this;
	}

	public function remove(CombatEffect $effect): CombatEffect {
		$this->removeItem($effect);

		return $this;
	}
}
