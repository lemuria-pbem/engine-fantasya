<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\GoliathWaterMessage;

final class GoliathWater extends AbstractUnitApply
{
	protected ?string $applyMessage = GoliathWaterMessage::class;
}
