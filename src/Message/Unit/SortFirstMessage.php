<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SortFirstMessage extends AbstractUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' reordered as first.';
	}
}
