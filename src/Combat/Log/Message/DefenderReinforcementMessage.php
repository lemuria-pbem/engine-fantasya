<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;
use Lemuria\Engine\Fantasya\Combat\Combat;

class DefenderReinforcementMessage extends AbstractReinforcementMessage
{
	#[Pure] public function getDebug(): string {
		return 'Defender unit ' . $this->unit . ' sends combatant ' . $this->combatant . ' with ' . $this->count .
			   ' fighters from ' . Combat::ROW_NAME[$this->battleRow] . ' to the front.';
	}
}
