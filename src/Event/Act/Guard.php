<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Effect\VanishEffect;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\UnguardMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Combat\BattleRow;

/**
 * A monster will guard the region with a 50% chance.
 * If it is already guarding, it will keep guarding with an 80% chance.
 */
class Guard implements Act
{
	use ActTrait;
	use MessageTrait;

	protected const GUARD = 0.5;

	protected const UNGUARD = 0.2;

	protected bool $isGuarding;

	public function IsGuarding(): bool {
		return $this->isGuarding;
	}

	public function act(): static {
		if ($this->hasVanishEffect()) {
			$this->isGuarding = true;
		} else {
			$this->isGuarding = $this->unit->IsGuarding();
			if ($this->isGuarding) {
				if (randChance(self::UNGUARD)) {
					$this->isGuarding = false;
					$this->unit->setIsGuarding(false);
					$this->message(UnguardMessage::class, $this->unit);
				}
			} else {
				if ($this->unit->BattleRow()->value >= BattleRow::Defensive->value && randChance(self::GUARD)) {
					$this->isGuarding = true;
				}
			}
		}
		return $this;
	}

	private function hasVanishEffect(): bool {
		if ($this->unit->Size() > 0) {
			$effect = new VanishEffect(State::getInstance());
			return Lemuria::Score()->find($effect->setUnit($this->unit)) instanceof VanishEffect;
		}
		return false;
	}
}
