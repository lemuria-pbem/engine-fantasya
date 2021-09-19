<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class AttackerSideMessage extends AbstractBattleSideMessage
{
	#[Pure] public function getDebug(): string {
		return 'On the attacking side there are participants ' . implode(', ', $this->participants) . '.';
	}
}
