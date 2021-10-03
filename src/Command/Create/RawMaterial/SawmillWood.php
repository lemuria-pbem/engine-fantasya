<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create\RawMaterial;

use Lemuria\Engine\Fantasya\Message\Unit\SawmillUnusableMessage;

/**
 * Special implementation of command MACHEN Holz (create wood) when unit is in a Sawmill.
 *
 * - MACHEN Holz
 * - MACHEN <amount> Holz
 */
final class SawmillWood extends AbstractDoubleRawMaterial
{
	protected function addUnusableMessage(): void {
		$this->message(SawmillUnusableMessage::class);
	}
}
