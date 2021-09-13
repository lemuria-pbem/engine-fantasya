<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class BattleEndedInDrawMessage extends AbstractMessage
{
	public function __toString(): string {
		return 'Battle ended with both sides defeated each other.';
	}
}
