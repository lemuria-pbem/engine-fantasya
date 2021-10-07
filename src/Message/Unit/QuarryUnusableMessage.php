<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class QuarryUnusableMessage extends QuarryUnmaintainedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce stone in the quarry, not enough space in the shack.';
	}
}
