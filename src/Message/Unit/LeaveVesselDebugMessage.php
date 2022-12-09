<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class LeaveVesselDebugMessage extends LeaveVesselMessage
{
	protected Result $result = Result::DEBUG;
}
