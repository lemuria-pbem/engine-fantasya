<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use function Lemuria\randChance;
use Lemuria\Engine\Fantasya\Message\Unit\GuardMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UnguardMessage;
use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;

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

	public function act(): Guard {
		if ($this->unit->IsGuarding()) {
			if (randChance(self::UNGUARD)) {
				$this->unit->setIsGuarding(false);
				$this->message(UnguardMessage::class, $this->unit);
			}
		} else {
			if (randChance(self::GUARD)) {
				$this->unit->setIsGuarding(true);
				$this->message(GuardMessage::class, $this->unit);
			}
		}
		return $this;
	}
}
