<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackOnVesselMessage extends AttackOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot attack unit ' . $this->unit . ' on a vessel.';
	}
}
