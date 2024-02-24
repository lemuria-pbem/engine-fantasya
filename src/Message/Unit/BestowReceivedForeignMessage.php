<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class BestowReceivedForeignMessage extends BestowMessage
{
	protected Result $result = Result::Event;

	protected function create(): string {
		return 'Unit ' . $this->id . ' receives ' . $this->composition . ' ' . $this->unicum . ' from unit ' . $this->unit . '.';
	}
}
