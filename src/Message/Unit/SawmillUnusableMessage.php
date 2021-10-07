<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SawmillUnusableMessage extends SawmillUnmaintainedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce wood in the sawmill, not enough space in the cabin.';
	}
}
