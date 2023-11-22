<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\Apply\ElixirOfPowerMessage;

final class ElixirOfPower extends AbstractUnitApply
{
	public const float BONUS = 0.4;

	protected ?string $applyMessage = ElixirOfPowerMessage::class;
}
