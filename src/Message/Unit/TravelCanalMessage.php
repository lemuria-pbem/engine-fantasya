<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelCanalMessage extends TravelRegionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' moves the vessel through the canal in region ' . $this->region . '.';
	}
}
