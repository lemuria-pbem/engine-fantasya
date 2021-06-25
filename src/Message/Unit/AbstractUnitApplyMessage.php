<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

abstract class AbstractUnitApplyMessage extends AbstractUnitMessage
{
	protected string $level = Message::EVENT;
}
