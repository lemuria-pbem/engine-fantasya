<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RawMaterialCanMessage extends RawMaterialWantsMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' can produce ' . $this->quantity . '.';
	}
}
