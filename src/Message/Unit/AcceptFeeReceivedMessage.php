<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptFeeReceivedMessage extends AcceptFeePaidMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has received ' . $this->fee . ' as market fee from unit ' . $this->unit . '.';
	}
}
