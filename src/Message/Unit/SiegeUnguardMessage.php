<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SiegeUnguardMessage extends SiegeNotFoundMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' can no longer guard the region, it is trapped in a sieged construction..';
	}
}
