<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelPassMessage extends TravelRegionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is allowed to pass region ' . $this->region . '.';
	}
}
