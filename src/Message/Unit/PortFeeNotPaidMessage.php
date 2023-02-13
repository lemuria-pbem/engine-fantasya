<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class PortFeeNotPaidMessage extends PortFeePaidMessage
{
	protected Result $result = Result::Failure;
}
