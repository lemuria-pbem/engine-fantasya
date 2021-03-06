<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Sentinel;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Message\Unit\UnguardMessage;
use Lemuria\Engine\Fantasya\Message\Unit\UnguardNotGuardingMessage;

/**
 * Implementation of command BEWACHEN Nicht.
 *
 * The command resets the guarding mode.
 *
 * - BEWACHEN Nicht
 */
final class Unguard extends UnitCommand
{
	protected function run(): void {
		if ($this->unit->IsGuarding()) {
			$this->unit->setIsGuarding(false);
			$this->message(UnguardMessage::class);
		} else {
			$this->message(UnguardNotGuardingMessage::class);
		}
	}
}
