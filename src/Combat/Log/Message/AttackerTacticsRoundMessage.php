<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class AttackerTacticsRoundMessage extends AbstractMessage
{
	#[Pure] public function getDebug(): string {
		return 'Attacker gets first strike in tactics round.';
	}
}
