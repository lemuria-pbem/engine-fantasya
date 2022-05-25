<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class EveryoneHasFledMessage extends AbstractMessage
{
	public function getDebug(): string {
		return 'Everyone has fled before the battle could begin.';
	}
}
