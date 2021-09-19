<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class DefenderOverrunMessage extends AbstractOverrunMessage
{
	#[Pure] public function getDebug(): string {
		return 'Defender is overrun (need ' . $this->additional . ' more fighters in front row.';
	}
}
