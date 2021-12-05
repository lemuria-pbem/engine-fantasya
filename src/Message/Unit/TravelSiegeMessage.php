<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelSiegeMessage extends TravelTooHeavyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot travel due to a siege.';
	}
}
