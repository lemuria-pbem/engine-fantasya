<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackOwnUnitMessage extends AttackNotFoundMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot attack unit ' . $this->unit . ' of own party.';
	}
}
