<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptDemandPriceMessage extends AcceptOfferAmountMessage
{
	protected function create(): string {
		return 'Unit ' . $this->unit . ' does not accept our proposed price for the demand with ID ' . $this->trade . '.';
	}
}
