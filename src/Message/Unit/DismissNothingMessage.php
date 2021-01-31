<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class DismissNothingMessage extends DismissEmptyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is empty and cannot dismiss anything.';
	}
}
