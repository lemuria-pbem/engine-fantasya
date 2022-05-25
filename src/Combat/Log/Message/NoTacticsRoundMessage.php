<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class NoTacticsRoundMessage extends AbstractMessage
{
	public function getDebug(): string {
		return 'Both sides are tactically equal.';
	}
}
