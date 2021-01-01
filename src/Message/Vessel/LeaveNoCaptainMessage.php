<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Vessel;

class LeaveNoCaptainMessage extends AbstractVesselMessage
{
	protected function create(): string {
		return 'Vessel ' . $this->id . ' has been abandoned.';
	}
}
