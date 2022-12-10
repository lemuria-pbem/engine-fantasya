<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Engine\Message;

class LeaveConstructionDebugMessage extends LeaveConstructionMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected string $level = Message::DEBUG;
}
