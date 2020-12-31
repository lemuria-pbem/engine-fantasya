<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class RawMaterialCanMessage extends RawMaterialWantsMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' can produce ' . $this->production . ' ' . $this->commodity . '.';
	}
}
