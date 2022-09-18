<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptOfferAlreadyMessage extends AcceptNoTradeMessage
{
	protected function create(): string {
		return 'The offer with ID ' . $this->trade . ' has already been bought by another customer.';
	}
}
