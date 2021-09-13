<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;
use Lemuria\Engine\Fantasya\Combat\Combat;

class AttackerSplitMessage extends AbstractSplitMessage
{
	#[Pure] public function __toString(): string {
		return 'Attacker unit ' . $this->unit . ' sends ' . $this->count . ' fighters from ' .
			   Combat::ROW_NAME[$this->battleRow] . ' combatant ' . $this->from . ' to the front as combatant ' .
			   $this->to . '.';
	}
}
