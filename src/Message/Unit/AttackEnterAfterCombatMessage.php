<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackEnterAfterCombatMessage extends AttackLeaveConstructionCombatMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' returns to construction ' . $this->place . ' after battle.';
	}
}
