<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class BattleSpellFailedMessage extends BattleSpellNoAuraMessage
{
	public function getDebug(): string {
		return 'Unit ' . $this->unit . ' failed casting ' . $this->spell . '.';
	}
}
