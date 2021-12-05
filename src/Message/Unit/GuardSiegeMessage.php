<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GuardSiegeMessage extends GuardAlreadyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is in a sieged construction and cannot guard.';
	}
}
