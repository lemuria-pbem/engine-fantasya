<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Message;

class SpellbookWriteUnknownMessage extends SpellbookWriteMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' does not know the spell ' . $this->spell . '.';
	}
}
