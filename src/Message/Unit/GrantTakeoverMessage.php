<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GrantTakeoverMessage extends GrantMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has taken over command from unit ' . $this->target . '.';
	}
}
