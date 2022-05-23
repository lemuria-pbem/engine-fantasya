<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class DefenderHasFledMessage extends AbstractMessage
{
	public function getDebug(): string {
		return 'All defenders have fled and the battle is over.';
	}
}
