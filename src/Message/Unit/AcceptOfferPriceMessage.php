<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptOfferPriceMessage extends AcceptOfferAmountMessage
{
	protected function create(): string {
		return 'Unit ' . $this->unit . ' does not accept our proposed price for the offer with ID ' . $this->trade . '.';
	}
}
