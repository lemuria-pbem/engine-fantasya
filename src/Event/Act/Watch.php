<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Act;

use Lemuria\Engine\Fantasya\Event\Act;
use Lemuria\Engine\Fantasya\Event\ActTrait;
use Lemuria\Engine\Fantasya\Factory\MessageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\GuardMessage;

/**
 * A watching monster will always guard a region.
 */
class Watch implements Act
{
	use ActTrait;
	use MessageTrait;

	public function act(): Watch {
		if (!$this->unit->IsGuarding()) {
			$this->unit->setIsGuarding(true);
			$this->message(GuardMessage::class, $this->unit);
		}
		return $this;
	}
}
