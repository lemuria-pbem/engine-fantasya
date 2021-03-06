<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelIntoOceanMessage extends TravelIntoChaosMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot move ' . $this->direction . ' into the ocean.';
	}
}
