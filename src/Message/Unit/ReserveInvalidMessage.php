<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ReserveInvalidMessage extends ReserveOnlyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' foolishly tries to reserve ' . $this->reserve . '.';
	}
}
