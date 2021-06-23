<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\Apply\BerserkBloodMessage;

final class BerserkBlood extends AbstractUnitApply
{
	protected ?string $applyMessage = BerserkBloodMessage::class;

}
