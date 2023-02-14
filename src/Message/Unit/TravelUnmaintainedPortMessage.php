<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class TravelUnmaintainedPortMessage extends TravelUnpaidDemurrageMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot leave the unmaintained port ' . $this->direction . '.';
	}
}
