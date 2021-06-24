<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SortLastMessage extends AbstractUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' reordered as last.';
	}
}
