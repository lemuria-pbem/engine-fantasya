<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SortAfterCaptainMessage extends SortFirstMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' reordered after vessel captain.';
	}
}
