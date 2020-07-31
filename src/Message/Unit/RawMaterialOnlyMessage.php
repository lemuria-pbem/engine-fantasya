<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class RawMaterialOnlyMessage extends ProductOutputMessage
{
	protected string $level = Message::FAILURE;
}
