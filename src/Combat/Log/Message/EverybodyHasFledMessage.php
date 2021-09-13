<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class EverybodyHasFledMessage extends AbstractMessage
{
	public function __toString(): string {
		return 'Everybody has fled from the battlefield and the battle is over.';
	}
}
