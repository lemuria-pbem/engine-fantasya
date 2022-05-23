<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class AssaultPetrifiedMessage extends AssaultBlockMessage
{
	public function getDebug(): string {
		return $this->attacker . ' cannot hurt ' . $this->attacker . ' who is petrified.';
	}
}
