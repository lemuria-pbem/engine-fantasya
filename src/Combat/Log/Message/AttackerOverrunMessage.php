<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class AttackerOverrunMessage extends AbstractOverrunMessage
{
	public function getDebug(): string {
		return 'Attacker is overrun (need ' . $this->additional . ' more fighters in front row).';
	}
}
