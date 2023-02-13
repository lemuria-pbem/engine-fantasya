<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class PortFeeOnlyMessage extends PortFeeMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only pay a port fee of ' . $this->fee . ' to the harbour master.';
	}
}
