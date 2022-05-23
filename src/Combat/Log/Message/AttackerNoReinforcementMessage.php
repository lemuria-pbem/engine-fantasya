<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class AttackerNoReinforcementMessage extends AbstractMessage
{
	public function getDebug(): string {
		return 'Attacker has no more forces to reinforce the front.';
	}
}
