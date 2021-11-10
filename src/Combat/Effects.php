<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat;

use JetBrains\PhpStorm\Pure;

use Lemuria\Item;
use Lemuria\ItemSet;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\BattleSpell;

class Effects extends ItemSet
{
	use BuilderTrait;

	public function add(CombatEffect $effect): Effects {
		$this->addItem($effect);
		return $this;
	}

	public function remove(CombatEffect $effect): Effects {
		$this->removeItem($effect);
		return $this;
	}

	protected function createItem(string $class, int $count): Item {
		/** @var BattleSpell $battleSpell */
		$battleSpell = self::createSpell($class);
		return new CombatEffect($battleSpell, $count);
	}

	#[Pure] protected function isValidItem(Item $item): bool {
		return $item instanceof BattleSpell;
	}
}