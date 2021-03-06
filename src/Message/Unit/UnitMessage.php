<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class UnitMessage extends AbstractUnitMessage
{
	protected function create(): string {
		return 'Change unit to ' . $this->id . '.';
	}
}
