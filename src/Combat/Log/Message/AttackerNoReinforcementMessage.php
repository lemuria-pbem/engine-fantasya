<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class AttackerNoReinforcementMessage extends AbstractMessage
{
	#[Pure] public function getDebug(): string {
		return 'Attacker has no more forces to reinforce the front.';
	}
}
