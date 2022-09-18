<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptDemandMessage extends AcceptOfferMessage
{
	protected function create(): string {
		return 'Customer ' . $this->unit . ' accepted demand ' . $this->trade . ' and sold ' . $this->quantity . ' for ' . $this->payment . '.';
	}
}
