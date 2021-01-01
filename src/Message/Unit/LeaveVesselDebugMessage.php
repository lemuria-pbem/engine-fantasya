<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class LeaveVesselDebugMessage extends LeaveVesselMessage
{
	protected string $level = Message::DEBUG;
}
