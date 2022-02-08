<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Region;

class TravelAllowedRegionMessage extends TravelGuardedRegionMessage
{
	protected function create(): string {
		return 'The guards of party ' . $this->party . ' have allowed the unit ' . $this->unit . ' to pass.';
	}
}
