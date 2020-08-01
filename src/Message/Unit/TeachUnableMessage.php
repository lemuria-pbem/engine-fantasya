<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class TeachUnableMessage extends TeachRegionMessage
{
	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach unit ' . $this->student . ' anymore.';
	}
}
