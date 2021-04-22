<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SmashDamageRoadMessage extends RoadMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' damages the road to ' . $this->direction . ' in region ' . $this->region . ' and regains ' . $this->stones . '.';
	}
}
