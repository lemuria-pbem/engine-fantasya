<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class SmashRoadGuardedMessage extends SmashNoRoadMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot smash roads in guarded region ' . $this->region . '.';
	}
}
