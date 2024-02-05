<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RobLeaveVesselCombatMessage extends RobLeaveConstructionCombatMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' leaves the vessel ' . $this->place . ' for the robbery.';
	}
}
