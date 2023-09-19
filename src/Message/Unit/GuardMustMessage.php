<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GuardMustMessage extends GuardAlreadyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' must guard before it can block borders.';
	}
}
