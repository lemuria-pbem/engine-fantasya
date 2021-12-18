<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class VesselSpaceMessage extends VesselCreateMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot create a new ' . $this->ship . ' - there is no space in any port.';
	}
}
