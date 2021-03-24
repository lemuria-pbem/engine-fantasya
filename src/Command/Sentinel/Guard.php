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
			return;
		}
		foreach ($this->calculus()->weaponSkill() as $skill) {
			if ($skill->isGuard()) {
				$this->unit->setIsGuarding(true);
				$this->message(GuardMessage::class);
				return;
			}
		}
		$this->message(GuardWithoutWeaponMessage::class);
	}
}
