<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class LeaveVesselUnpaidDemurrageMessage extends LeaveVesselMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot leave the vessel ' . $this->vessel . ' because demurrage has not been paid.';
	}
}
