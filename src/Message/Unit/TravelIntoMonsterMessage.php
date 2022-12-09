<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class TravelIntoMonsterMessage extends TravelRegionMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->id . ' was ambushed in region ' . $this->region . ' by monsters and stopped.';
	}
}
