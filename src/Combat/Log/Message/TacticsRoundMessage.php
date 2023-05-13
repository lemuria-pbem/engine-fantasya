<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class TacticsRoundMessage extends AbstractMessage
{
	public function getDebug(): string {
		return 'Attacker gets first strike in tactics round.';
	}
}
