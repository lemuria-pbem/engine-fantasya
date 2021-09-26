<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

use JetBrains\PhpStorm\Pure;

class AttackerHasFledMessage extends AbstractMessage
{
	#[Pure] public function getDebug(): string {
		return 'All attackers have fled and the battle is over.';
	}
}
