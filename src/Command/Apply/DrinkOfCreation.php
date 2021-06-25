<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\DrinkOfCreationMessage;

final class DrinkOfCreation extends AbstractUnitApply
{
	protected ?string $applyMessage = DrinkOfCreationMessage::class;
}
