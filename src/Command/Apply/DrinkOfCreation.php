<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\Apply\DrinkOfCreationMessage;

final class DrinkOfCreation extends AbstractUnitApply
{
	protected ?string $applyMessage = DrinkOfCreationMessage::class;
}
