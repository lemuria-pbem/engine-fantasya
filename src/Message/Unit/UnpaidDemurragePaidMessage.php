<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class UnpaidDemurragePaidMessage extends UnpaidDemurrageMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Unit ' . $this->id . ' pays outstanding demurrage ' . $this->fee . '.';
	}
}
