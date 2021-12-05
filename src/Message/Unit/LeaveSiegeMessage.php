<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class LeaveSiegeMessage extends LeaveNotMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot leave the sieged construction.';
	}
}
