<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class StealNotHereMessage extends StealOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot find ' . $this->unit . ' to steal from.';
	}
}
