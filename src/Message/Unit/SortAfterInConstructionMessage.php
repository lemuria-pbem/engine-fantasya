<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class SortAfterInConstructionMessage extends SortAfterMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' reordered after unit ' . $this->other . ' in construction.';
	}
}
