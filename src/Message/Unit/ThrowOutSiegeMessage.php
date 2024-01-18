<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ThrowOutSiegeMessage extends ThrowOutOwnMessage
{
	protected function create(): string {
		return 'Unit ' . $this->unit . ' cannot leave the sieged construction.';
	}
}
