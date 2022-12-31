<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\GuardMessage;
use Lemuria\Model\Fantasya\Combat\BattleRow;

/**
 * A watching monster will always guard a region.
 */
class Watch implements Act
{
	use ActTrait;
	use MessageTrait;

	protected bool $isGuarding;

	public function IsGuarding(): bool {
		return $this->isGuarding;
	}

	public function act(): Watch {
		$this->isGuarding = $this->unit->IsGuarding();
		if (!$this->isGuarding && $this->unit->BattleRow()->value >= BattleRow::Defensive->value) {
			$this->unit->setIsGuarding(true);
			$this->message(GuardMessage::class, $this->unit);
		}
		return $this;
	}
}
