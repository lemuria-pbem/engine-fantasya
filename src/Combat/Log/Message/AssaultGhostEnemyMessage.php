<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class AssaultGhostEnemyMessage extends AssaultBlockMessage
{
	public function getDebug(): string {
		return $this->attacker . ' attacks the ghost of ' . $this->defender . '.';
	}
}
