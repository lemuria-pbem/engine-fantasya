<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class DefenderSideMessage extends AbstractBattleSideMessage
{
	public function getDebug(): string {
		return 'On the defending side there are participants ' . implode(', ', $this->participants) . ':';
	}
}
