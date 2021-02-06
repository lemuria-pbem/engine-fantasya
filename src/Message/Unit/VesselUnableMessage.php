<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class VesselUnableMessage extends VesselCreateMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is not skilled enought to create a new ' . $this->ship . '.';
	}
}
