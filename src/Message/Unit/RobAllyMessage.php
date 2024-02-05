<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RobAllyMessage extends RobOwnUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot attack allied unit ' . $this->unit . '.';
	}
}
