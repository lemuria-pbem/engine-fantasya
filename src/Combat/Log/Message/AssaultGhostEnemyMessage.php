<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class AssaultGhostEnemyMessage extends AssaultBlockMessage
{
	#[Pure] public function getDebug(): string {
		return $this->attacker . ' attacks the ghost of ' . $this->defender . '.';
	}
}
