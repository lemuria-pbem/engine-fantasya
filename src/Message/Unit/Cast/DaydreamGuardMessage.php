<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

class DaydreamGuardMessage extends DaydreamConcentrateMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot concentrate anymore and abandons its watch.';
	}
}
