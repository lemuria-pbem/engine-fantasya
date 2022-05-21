<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

class TravelAirshipMessage extends TravelVesselMessage
{
	protected function create(): string {
		return 'Vessel ' . $this->vessel . ' has been spotted flying above region ' . $this->id . '.';
	}
}
