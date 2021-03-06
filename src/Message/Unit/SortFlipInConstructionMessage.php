<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SortFlipInConstructionMessage extends SortAfterMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' flipped with unit ' . $this->other . ' in construction.';
	}
}
