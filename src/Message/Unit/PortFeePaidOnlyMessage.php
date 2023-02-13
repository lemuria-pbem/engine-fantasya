<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class PortFeePaidOnlyMessage extends PortFeePaidMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' can only pay a port fee of ' . $this->fee . '.';
	}
}
