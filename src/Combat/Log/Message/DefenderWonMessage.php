<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class DefenderWonMessage extends AbstractMessage
{
	public function __toString(): string {
		return 'Defender has won the battle, attacker is defeated.';
	}
}
