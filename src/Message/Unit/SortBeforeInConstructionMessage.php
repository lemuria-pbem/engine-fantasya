<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class SortBeforeInConstructionMessage extends SortAfterMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' reordered before unit ' . $this->other . ' in construction.';
	}
}
