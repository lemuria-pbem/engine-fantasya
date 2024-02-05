<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RobOnVesselMessage extends RobOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot assault and rob unit ' . $this->unit . ' on a vessel.';
	}
}
