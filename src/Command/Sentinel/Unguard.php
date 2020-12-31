<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Sentinel;

use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Engine\Lemuria\Message\Unit\UnguardMessage;
use Lemuria\Engine\Lemuria\Message\Unit\UnguardNotGuardingMessage;

/**
 * Implementation of command BEWACHEN Nicht.
 *
 * The command resets the guarding mode.
 *
 * - BEWACHEN Nicht
 */
final class Unguard extends UnitCommand {

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		if ($this->unit->IsGuarding()) {
			$this->unit->setIsGuarding(false);
			$this->message(UnguardMessage::class);
		} else {
			$this->message(UnguardNotGuardingMessage::class);
		}
	}
}
