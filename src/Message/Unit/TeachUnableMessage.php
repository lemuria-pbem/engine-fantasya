<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TeachUnableMessage extends TeachRegionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach unit ' . $this->student . ' anymore.';
	}
}
