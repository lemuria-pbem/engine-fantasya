<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackInCastleMessage extends AttackOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot attack unit ' . $this->unit . ' in a castle.';
	}
}
