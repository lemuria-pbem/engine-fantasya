<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class DrinkOfCreationMessage extends AbstractUnitApplyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' feels full of energy and zest for action.';
	}
}
