<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class BestowReceivedMessage extends BestowMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' receives ' . $this->composition . ' ' . $this->unicum . ' from unit ' . $this->unit . '.';
	}
}
