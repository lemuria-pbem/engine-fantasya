<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class AttackerWonMessage extends AbstractMessage
{
	public function getDebug(): string {
		return 'Attacker has won the battle, defender is defeated.';
	}
}
