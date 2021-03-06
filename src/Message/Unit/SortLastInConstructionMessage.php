<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SortLastInConstructionMessage extends SortLastMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' reordered as last in construction.';
	}
}
