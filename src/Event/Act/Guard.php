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

	protected const float GUARD = 0.5;

	protected const float UNGUARD = 0.2;

	protected bool $isGuarding;

	protected float $guardChance = self::GUARD;

	protected float $unguardChance = self::UNGUARD;

	public function IsGuarding(): bool {
		return $this->isGuarding;
	}

	public function guard(float $chance): static {
		$this->guardChance = $chance;
		return $this;
	}

	public function unguard(float $chance): static {
		$this->unguardChance = $chance;
		return $this;
	}

	public function act(): static {
		if ($this->hasVanishEffect()) {
			$this->isGuarding = true;
		} else {
			$this->isGuarding = $this->unit->IsGuarding();
			if ($this->isGuarding) {
				if (randChance($this->unguardChance)) {
					$this->isGuarding = false;
					$this->unit->setIsGuarding(false);
					$this->message(UnguardMessage::class, $this->unit);
				}
			} else {
				if ($this->unit->BattleRow()->value >= BattleRow::Defensive->value && randChance($this->guardChance)) {
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
