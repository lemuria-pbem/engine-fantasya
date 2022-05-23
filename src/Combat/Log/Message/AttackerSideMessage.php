<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class AttackerSideMessage extends AbstractBattleSideMessage
{
	public function getDebug(): string {
		return 'On the attacking side there are participants ' . implode(', ', $this->participants) . ':';
	}
}
