<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\SevenLeagueTeaMessage;

final class SevenLeagueTea extends AbstractUnitApply
{
	protected ?string $applyMessage = SevenLeagueTeaMessage::class;
}
