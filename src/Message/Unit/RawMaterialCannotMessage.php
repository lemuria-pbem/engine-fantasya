<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class RawMaterialCannotMessage extends RawMaterialWantsMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot produce more than ' . $this->production . ' ' . $this->commodity . '.';
	}
}
