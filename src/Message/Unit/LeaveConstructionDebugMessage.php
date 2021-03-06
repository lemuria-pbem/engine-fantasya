<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class LeaveConstructionDebugMessage extends LeaveConstructionMessage
{
	protected string $level = Message::DEBUG;
}
