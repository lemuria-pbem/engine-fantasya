<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackAllyMessage extends AttackOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot attack allied unit ' . $this->unit . '.';
	}
}
