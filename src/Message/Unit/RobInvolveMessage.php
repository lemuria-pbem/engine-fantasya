<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class RobInvolveMessage extends RobMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' attacks unit ' . $this->unit . '.';
	}
}
