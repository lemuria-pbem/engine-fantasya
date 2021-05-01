<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class StealDiscoveredMessage extends StealOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' failed to steal from unit ' . $this->unit . ' and was discovered.';
	}
}
