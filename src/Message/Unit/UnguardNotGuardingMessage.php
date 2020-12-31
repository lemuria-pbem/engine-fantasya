<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class UnguardNotGuardingMessage extends AbstractUnitMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is not guarding, unguarding not possible.';
	}
}
