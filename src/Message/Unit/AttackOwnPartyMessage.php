<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AttackOwnPartyMessage extends AttackSelfMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot attack own party.';
	}
}
