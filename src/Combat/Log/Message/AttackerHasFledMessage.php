<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class AttackerHasFledMessage extends AbstractMessage
{
	public function getDebug(): string {
		return 'All attackers have fled and the battle is over.';
	}
}
