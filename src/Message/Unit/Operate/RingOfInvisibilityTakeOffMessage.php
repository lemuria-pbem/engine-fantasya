<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

class RingOfInvisibilityTakeOffMessage extends RingOfInvisibilityApplyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' takes off the ' . $this->composition . ' ' . $this->unicum . ' to become visible again.';
	}
}
