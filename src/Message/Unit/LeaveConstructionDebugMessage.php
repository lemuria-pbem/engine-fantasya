<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class LeaveConstructionDebugMessage extends LeaveConstructionMessage
{
	protected Result $result = Result::Debug;
}
