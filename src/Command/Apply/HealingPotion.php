<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\Apply\HealingPotionMessage;

final class HealingPotion extends AbstractUnitApply
{
	protected ?string $applyMessage = HealingPotionMessage::class;
}
