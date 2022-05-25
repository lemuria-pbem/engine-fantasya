<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class DefenderNoReinforcementMessage extends AbstractMessage
{
	public function getDebug(): string {
		return 'Defender has no more forces to reinforce the front.';
	}
}
