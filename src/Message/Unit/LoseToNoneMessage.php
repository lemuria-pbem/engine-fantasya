<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class LoseToNoneMessage extends AbstractUnitMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' throws away all its property.';
	}
}
