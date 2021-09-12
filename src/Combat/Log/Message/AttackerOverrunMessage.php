<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class AttackerOverrunMessage extends AbstractOverrunMessage
{
	#[Pure] public function __toString(): string {
		return 'Attacker is overrun (need ' . $this->additional . ' more fighters in front row.';
	}
}
