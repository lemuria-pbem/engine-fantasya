<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RoadMessage extends RoadCompletedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' uses ' . $this->stones . ' in construction of the road to ' . $this->direction . ' in region ' . $this->region . '.';
	}
}
