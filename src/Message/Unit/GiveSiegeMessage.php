<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GiveSiegeMessage extends GiveFailedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot give anything to unit ' . $this->recipient . ' due to a siege.';
	}
}
