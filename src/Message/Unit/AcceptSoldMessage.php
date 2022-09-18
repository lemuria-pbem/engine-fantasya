<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class AcceptSoldMessage extends AcceptOfferMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' bought ' . $this->quantity . ' from merchant ' . $this->unit . ' for ' . $this->payment . '.';
	}
}
