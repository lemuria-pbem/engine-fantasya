<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class BattleSpellMessage extends BattleSpellRemoveMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' will cast ' . $this->spell . ' in combat.';
	}
}
