<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class DefenderTacticsRoundMessage extends AbstractMessage
{
	public function getDebug(): string {
		return 'Defender gets first strike in tactics round.';
	}
}
