<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combat;

class SummonBeingMessage extends AbstractReinforcementMessage
{
	public function getDebug(): string {
		return 'Unit ' . $this->unit . ' consisting of ' . $this->count . ' fighters in ' .
			   Combat::ROW_NAME[$this->battleRow] . ' row has been summoned as combatant '. $this->combatant . '.';
	}
}
