<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class QuacksalverOnlyMessage extends QuacksalverMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' could only earn ' . $this->silver . ' with Quacksalver.';
	}
}
