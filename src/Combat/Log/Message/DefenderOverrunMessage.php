<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class DefenderOverrunMessage extends AbstractOverrunMessage
{
	public function getDebug(): string {
		return 'Defender is overrun (need ' . $this->additional . ' more fighters in front row.';
	}
}
