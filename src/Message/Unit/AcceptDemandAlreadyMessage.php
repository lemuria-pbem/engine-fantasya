<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptDemandAlreadyMessage extends AcceptNoTradeMessage
{
	protected function create(): string {
		return 'The demand with ID ' . $this->trade . ' has already been delivered by another customer.';
	}
}
