<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combat;

class DefenderReinforcementWeaponMessage extends AbstractReinforcementWeaponMessage
{
	public function getDebug(): string {
		return 'Defender unit ' . $this->unit . ' sends combatant ' . $this->combatant . ' with ' . $this->count .
			   ' fighters (with ' . $this->weapon . ') from ' . Combat::ROW_NAME[$this->battleRow] . ' to the front.';
	}
}
