<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SpyDiscoveredMessage extends SpyOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' failed to spy on unit ' . $this->unit . ' and was discovered.';
	}
}
