<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SortWithForeignerMessage extends SortNotInRegionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot sort with foreign unit ' . $this->other . '.';
	}
}
