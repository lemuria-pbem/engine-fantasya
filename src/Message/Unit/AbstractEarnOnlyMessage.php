<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

abstract class AbstractEarnOnlyMessage extends AbstractEarnMessage
{
	protected string $level = Message::FAILURE;
}
