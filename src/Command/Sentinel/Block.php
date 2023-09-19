<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Sentinel;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Unit\GuardBorderMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GuardMustMessage;

/**
 * Implementation of command BEWACHEN.
 *
 * The command sets the unit in guarding mode.
 *
 * - BEWACHEN <direction>
 */
final class Block extends UnitCommand
{
	use SiegeTrait;

	protected function run(): void {
		if ($this->unit->IsGuarding()) {
			$direction = $this->context->Factory()->direction($this->phrase->getParameter());
			$this->unit->setIsGuarding($direction);
			$this->message(GuardBorderMessage::class)->p($direction->value);
		} else {
			$this->message(GuardMustMessage::class);
		}
	}
}
