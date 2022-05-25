<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class CombatantWeaponDegradedMessage extends CombatantWeaponMessage
{
	public function getDebug(): string {
		return 'The weapon (' . $this->weapon . ') of combatant ' . $this->combatant . ' degrades.';
	}
}
