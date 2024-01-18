<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class ThrowOutSuperiorityMessage extends ThrowOutOwnMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected function create(): string {
		return 'We have no superiority over unit ' . $this->id . ' to throw it out.';
	}
}
