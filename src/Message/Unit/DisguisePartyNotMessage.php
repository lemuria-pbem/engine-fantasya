<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class DisguisePartyNotMessage extends DisguiseMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' will not hide its party.';
	}
}
