<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class BattleEndsMessage extends AbstractMessage
{
	public function getDebug(): string {
		return 'The battle is over.';
	}
}
