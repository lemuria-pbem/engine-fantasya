<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message;

abstract class AbstractCastMessage extends AbstractUnitMessage
{
	protected string $level = Message::EVENT;
}
