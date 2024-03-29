<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combat;

class AttackerReinforcementMessage extends AbstractReinforcementMessage
{
	public function getDebug(): string {
		return 'Attacker unit ' . $this->unit . ' sends combatant ' . $this->combatant . ' with ' . $this->count .
			   ' fighters from ' . Combat::ROW_NAME[$this->battleRow] . ' to the front.';
	}
}
