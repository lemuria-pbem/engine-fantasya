<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class CombatantWeaponDegradedMessage extends CombatantWeaponMessage
{
	#[Pure] public function getDebug(): string {
		return 'The weapon (' . $this->weapon . ') of combatant ' . $this->combatant . ' degrades.';
	}
}
