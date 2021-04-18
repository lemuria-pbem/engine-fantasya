<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class SmashRoadGuardedMessage extends SmashNoRoadMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot smash roads in guarded region ' . $this->region . '.';
	}
}
