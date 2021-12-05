<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SiegeNotGuardingMessage extends SiegeNotFightingMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' must be guarding to participate in a siege.';
	}
}
