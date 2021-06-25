<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GiveReceivedFromForeignMessage extends GiveReceivedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' receives ' . $this->gift . ' from unit ' . $this->from . '.';
	}
}
