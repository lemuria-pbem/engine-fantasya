<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Sentinel;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Message\Unit\GuardAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GuardMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GuardWithoutWeaponMessage;

/**
 * Implementation of command BEWACHEN.
 *
 * The command sets the unit in guarding mode.
 *
 * - BEWACHEN
 */
final class Guard extends UnitCommand
{
	protected function run(): void {
		if ($this->unit->IsGuarding()) {
			$this->message(GuardAlreadyMessage::class);
		} else {
			if ($this->calculus()->weaponSkill()->isGuard()) {
				$this->message(GuardWithoutWeaponMessage::class);
			} else {
				$this->unit->setIsGuarding(true);
				$this->message(GuardMessage::class);
			}
		}
	}
}
