<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelNoRidingMessage extends TravelTooHeavyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is not good enough in riding to travel.';
	}
}
