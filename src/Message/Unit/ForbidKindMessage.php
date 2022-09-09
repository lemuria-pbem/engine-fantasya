<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ForbidKindMessage extends AllowKindMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' forbids to trade ' . $this->commodity . '.';
	}
}
