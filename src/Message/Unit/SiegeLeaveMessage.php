<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SiegeLeaveMessage extends SiegeNotFightingMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' leaves the construction for a siege.';
	}
}
