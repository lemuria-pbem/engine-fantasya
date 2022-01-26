<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Combat\Combat;

class AttackerReinforcementWeaponMessage extends AbstractReinforcementWeaponMessage
{
	#[Pure] public function getDebug(): string {
		return 'Attacker unit ' . $this->unit . ' sends combatant ' . $this->combatant . ' with ' . $this->count .
			   ' fighters (with ' . $this->weapon . ') from ' . Combat::ROW_NAME[$this->battleRow] . ' to the front.';
	}
}
