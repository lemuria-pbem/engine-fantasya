<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SpyNotHereMessage extends SpyOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->unit . ' cannot find ' . $this->unit . ' to spy.';
	}
}
