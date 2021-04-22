<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RoadNoRessourcesMessage extends RoadAlreadyCompletedMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has no stones for construction of the road to ' . $this->direction . ' in region ' . $this->region . '.';
	}
}
