<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelRoadMessage extends AbstractUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has moved over road, it can move one more region.';
	}
}
