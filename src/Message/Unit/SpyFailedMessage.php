<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class SpyFailedMessage extends SpyOwnUnitMessage
{
	protected Reliability $reliability = Reliability::Unreliable;

	protected function create(): string {
		return 'Unit ' . $this->id . ' could not spy on unit ' . $this->unit . '.';
	}
}
