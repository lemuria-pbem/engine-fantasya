<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\ElixirOfPowerMessage;

final class ElixirOfPower extends AbstractUnitApply
{
	protected ?string $applyMessage = ElixirOfPowerMessage::class;
}
