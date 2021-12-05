<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class DismissSiegeMessage extends DismissEmptyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot dismiss anything, the construction is sieged.';
	}
}
