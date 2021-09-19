<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class FleeFromBattleMessage extends AbstractFleeFromBattleMessage
{
	public function getDebug(): string {
		return 'Combatant ' . $this->combatant . ' flees from battle.';
	}
}
