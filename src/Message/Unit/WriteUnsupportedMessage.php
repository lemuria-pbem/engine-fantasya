<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class WriteUnsupportedMessage extends ReadUnsupportedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot write to ' . $this->composition . ' ' . $this->unicum . '.';
	}
}
