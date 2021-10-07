<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create\RawMaterial;

use Lemuria\Engine\Fantasya\Message\Unit\QuarryUnmaintainedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\QuarryUnusableMessage;

/**
 * Special implementation of command MACHEN Stein (create stone) when unit is in a Quarry.
 *
 * - MACHEN Stein
 * - MACHEN <amount> Stein
 */
final class QuarryStone extends AbstractDoubleRawMaterial
{
	protected function addUnusableMessage(): void {
		$this->message(QuarryUnusableMessage::class);
	}

	protected function addUnmaintainedMessage(): void {
		$this->message(QuarryUnmaintainedMessage::class);
	}
}
