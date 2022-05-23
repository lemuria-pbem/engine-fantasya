<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use Lemuria\Engine\Fantasya\Combat\Combat;

class DefenderSplitMessage extends AbstractSplitMessage
{
	public function getDebug(): string {
		return 'Defender unit ' . $this->unit . ' sends ' . $this->count . ' fighters from ' .
			   Combat::ROW_NAME[$this->battleRow] . ' combatant ' . $this->from . ' to the front as combatant ' .
			   $this->to . '.';
	}
}
