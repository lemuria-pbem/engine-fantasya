<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class DefenderHasFledMessage extends AbstractMessage
{
	#[Pure] public function getDebug(): string {
		return 'All defenders have fled and the battle is over.';
	}
}
