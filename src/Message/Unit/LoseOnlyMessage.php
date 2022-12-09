<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class LoseOnlyMessage extends LoseMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can throw away ' . $this->quantity . ' only.';
	}
}
