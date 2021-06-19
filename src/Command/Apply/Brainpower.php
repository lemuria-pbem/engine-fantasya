<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\BrainpowerMessage;

final class Brainpower extends AbstractUnitApply
{
	protected ?string $applyMessage = BrainpowerMessage::class;
}
