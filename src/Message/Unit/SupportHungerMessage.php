<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SupportHungerMessage extends SupportNothingMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is starving.';
	}
}
