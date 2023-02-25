<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelVesselTooHeavyMessage extends TravelNotCaptainMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot steer the overloaded vessel ' . $this->vessel . '.';
	}
}
