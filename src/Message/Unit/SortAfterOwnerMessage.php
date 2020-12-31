<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class SortAfterOwnerMessage extends SortFirstMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' reordered after construction owner.';
	}
}
