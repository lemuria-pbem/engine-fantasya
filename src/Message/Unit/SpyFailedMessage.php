<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SpyFailedMessage extends SpyOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' could not spy on unit ' . $this->unit . '.';
	}
}
