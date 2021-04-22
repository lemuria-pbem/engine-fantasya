<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class VesselAlreadyFinishedMessage extends VesselResourcesMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' has nothing to do, vessel ' . $this->vessel . ' is already finished.';
	}
}
