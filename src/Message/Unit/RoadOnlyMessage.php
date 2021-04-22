<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RoadOnlyMessage extends RoadCompletedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' can only use ' . $this->stones . ' in construction of the road to ' . $this->direction . ' in region ' . $this->region . '.';
	}
}
