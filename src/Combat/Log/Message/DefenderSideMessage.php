<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class DefenderSideMessage extends AbstractBattleSideMessage
{
	#[Pure] public function __toString(): string {
		return 'On the defending side there are participants ' . implode(', ', $this->participants) . '.';
	}
}
