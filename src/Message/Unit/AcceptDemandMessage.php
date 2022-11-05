<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptDemandMessage extends AcceptOfferMessage
{
	protected function create(): string {
		return 'Customer ' . $this->unit . ' accepts demand ' . $this->trade . ' and sells ' . $this->quantity . ' for ' . $this->payment . '.';
	}
}
