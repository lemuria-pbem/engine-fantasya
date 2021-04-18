<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SmashNoRoadToMessage extends RoadAlreadyCompletedMessage
{
	protected function create(): string {
		return 'There is no road to ' . $this->direction . ' in region ' . $this->region . ' to smash.';
	}
}
