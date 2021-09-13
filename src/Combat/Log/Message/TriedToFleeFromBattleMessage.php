<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class TriedToFleeFromBattleMessage extends AbstractFleeFromBattleMessage
{
	public function __toString(): string {
		return 'Combatant ' . $this->combatant . ' tried to flee from battle.';
	}
}
