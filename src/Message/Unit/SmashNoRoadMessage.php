<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SmashNoRoadMessage extends RoadInOceanMessage
{
	protected function create(): string {
		return 'There is no road in region ' . $this->region . ' to smash.';
	}
}
