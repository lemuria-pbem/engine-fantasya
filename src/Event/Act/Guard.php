<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\UnguardMessage;

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

	public function act(): Guard {
		$this->isGuarding = $this->unit->IsGuarding();
		if ($this->isGuarding) {
			if (randChance(self::UNGUARD)) {
				$this->isGuarding = false;
				$this->unit->setIsGuarding(false);
				$this->message(UnguardMessage::class, $this->unit);
			}
		} else {
			if (randChance(self::GUARD)) {
				$this->isGuarding = true;
			}
		}
		return $this;
	}
}
