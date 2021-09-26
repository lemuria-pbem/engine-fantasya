<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class EveryoneHasFledMessage extends AbstractMessage
{
	#[Pure] public function getDebug(): string {
		return 'Everyone has fled before the battle could begin.';
	}
}
