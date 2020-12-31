<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class NameNotInVesselMessage extends NameNotInConstructionMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is not in any vessel and thus cannot rename it.';
	}
}
