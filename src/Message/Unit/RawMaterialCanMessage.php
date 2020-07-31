<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class RawMaterialCanMessage extends RawMaterialWantsMessage
{
	/**
	 * @return string
	 */
	protected function create(): string {
		return 'Unit ' . $this->Id() . ' can produce ' . $this->production . ' ' . $this->commodity . '.';
	}
}
