<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RobBoardAfterCombatMessage extends RobLeaveConstructionCombatMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' returns to vessel ' . $this->place . ' after the robbery.';
	}
}
