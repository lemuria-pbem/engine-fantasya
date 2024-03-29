<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class SmashLeaveVesselMessage extends SmashNotVesselOwnerMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has left the vessel ' . $this->vessel . ' before it is destroyed.';
	}
}
