<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Combat\Log\Message;

class TakeTrophiesMessage extends TakeLootMessage
{
	public function getDebug(): string {
		return $this->unit . ' takes trophies: ' . $this->loot . '.';
	}
}
