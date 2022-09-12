<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class DemandMessage extends OfferMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' creates the new demand ' . $this->trade . '.';
	}
}
