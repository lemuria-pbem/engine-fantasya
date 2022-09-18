<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptDemandRemovedMessage extends AcceptOfferRemovedMessage
{
	protected function create(): string {
		return 'Demand ' . $this->trade . ' has been terminated.';
	}
}
