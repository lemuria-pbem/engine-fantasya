<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

abstract class AbstractEarnOnlyMessage extends AbstractEarnMessage
{
	protected Result $result = Result::FAILURE;
}
