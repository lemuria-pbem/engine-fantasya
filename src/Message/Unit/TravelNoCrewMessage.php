<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelNoCrewMessage extends TravelNotCaptainMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot steer the vessel ' . $this->vessel . ' without a crew that is skilled enough.';
	}
}
