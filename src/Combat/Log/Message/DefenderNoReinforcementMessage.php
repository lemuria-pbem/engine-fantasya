<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class DefenderNoReinforcementMessage extends AbstractMessage
{
	#[Pure] public function __toString(): string {
		return 'Defender has no more forces to reinforce the front.';
	}
}
