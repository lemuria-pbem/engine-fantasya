<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptDemandAmountMessage extends AcceptOfferAmountMessage
{
	protected function create(): string {
		return 'Unit ' . $this->unit . ' does not demand the requested amount in the trade with ID ' . $this->trade . '.';
	}
}
