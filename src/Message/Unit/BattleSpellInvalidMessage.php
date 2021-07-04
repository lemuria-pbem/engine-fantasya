<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class BattleSpellInvalidMessage extends BattleSpellNotSetMessage
{
	protected function create(): string {
		return 'The spell ' . $this->spell . ' is not a combat spell.';

	}
}
