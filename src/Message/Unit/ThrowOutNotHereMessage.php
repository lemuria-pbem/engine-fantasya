<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class ThrowOutNotHereMessage extends ThrowOutOwnMessage
{
	protected function create(): string {
		return 'We cannot throw out unit ' . $this->id . ' that is not here.';
	}
}
