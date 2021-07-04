<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message;

abstract class AbstractApplyMessage extends AbstractUnitMessage
{
	protected string $level = Message::EVENT;
}
