<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackBoardAfterCombatMessage extends AttackLeaveConstructionCombatMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' returns to vessel ' . $this->place . ' after battle.';
	}
}
