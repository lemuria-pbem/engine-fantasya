<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class ManagedToFleeFromBattleMessage extends AbstractFleeFromBattleMessage
{
	public function getDebug(): string {
		return 'Combatant ' . $this->combatant . ' managed to flee from battle.';
	}
}
