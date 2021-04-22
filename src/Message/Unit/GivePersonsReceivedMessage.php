<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GivePersonsReceivedMessage extends GivePersonsMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' receives ' . $this->persons . ' persons from unit ' . $this->recipient . '.';
	}
}
