<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RepeatNotMessage extends RepeatMessage
{
	protected function create(): string {
		return 'The trade ' . $this->trade . ' will not be repeated.';
	}
}
