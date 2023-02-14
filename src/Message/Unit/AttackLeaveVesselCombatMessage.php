<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackLeaveVesselCombatMessage extends AttackLeaveConstructionCombatMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' leaves the vessel ' . $this->place . ' for battle.';
	}
}
