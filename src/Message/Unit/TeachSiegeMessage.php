<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TeachSiegeMessage extends TeachRegionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach unit ' . $this->student . ' due to a siege.';
	}
}
