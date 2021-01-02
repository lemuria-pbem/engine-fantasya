<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

abstract class AbstractEarnOnlyMessage extends AbstractEarnMessage
{
	protected string $level = Message::FAILURE;
}
