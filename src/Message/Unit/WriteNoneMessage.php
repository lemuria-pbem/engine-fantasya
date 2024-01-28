<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class WriteNoneMessage extends ReadUnsupportedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has no capacity left to write to ' . $this->composition . ' ' . $this->unicum . '.';
	}
}
