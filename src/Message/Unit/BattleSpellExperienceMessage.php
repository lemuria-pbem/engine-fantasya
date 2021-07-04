<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class BattleSpellExperienceMessage extends BattleSpellNotSetMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has not enough experience to cast ' . $this->spell . ' in combat.';
	}
}
