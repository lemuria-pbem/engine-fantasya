<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RobProtectedPartyMessage extends RobOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot rob unit ' . $this->unit . ' of protected party.';
	}
}
