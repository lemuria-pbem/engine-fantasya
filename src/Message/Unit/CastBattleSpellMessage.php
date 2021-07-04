<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class CastBattleSpellMessage extends CastExperienceMessage
{
	protected function create(): string {
		return 'Spell ' . $this->spell . ' can be used in battle only.';
	}
}
