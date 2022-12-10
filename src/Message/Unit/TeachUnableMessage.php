<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;

class TeachUnableMessage extends TeachRegionMessage
{
	protected Reliability $reliability = Reliability::Determined;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach unit ' . $this->student . ' anymore.';
	}
}
