<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class StealNothingMessage extends StealOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->unit . ' has no silver that we could steal.';
	}
}
