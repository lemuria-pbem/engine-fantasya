<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class AssaultPetrifiedMessage extends AssaultBlockMessage
{
	#[Pure] public function getDebug(): string {
		return $this->attacker . ' cannot hurt ' . $this->attacker . ' who is petrified.';
	}
}
