<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create\RawMaterial;

use Lemuria\Engine\Fantasya\Message\Unit\MineUnmaintainedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\MineUnusableMessage;

/**
 * Special implementation of command MACHEN Eisen (create iron) when unit is in a Mine.
 *
 * - MACHEN Eisen
 * - MACHEN <amount> Eisen
 */
final class MineIron extends AbstractDoubleRawMaterial
{
	protected function addUnusableMessage(): void {
		$this->message(MineUnusableMessage::class);
	}

	protected function addUnmaintainedMessage(): void {
		$this->message(MineUnmaintainedMessage::class);
	}
}
