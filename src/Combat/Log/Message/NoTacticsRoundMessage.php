<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class NoTacticsRoundMessage extends AbstractMessage
{
	#[Pure] public function getDebug(): string {
		return 'Both sides are tactically equal.';
	}
}
