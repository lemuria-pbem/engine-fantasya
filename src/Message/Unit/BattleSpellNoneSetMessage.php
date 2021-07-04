<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class BattleSpellNoneSetMessage extends BattleSpellNoMagicianMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has not set any combat spells that can be removed.';
	}
}
