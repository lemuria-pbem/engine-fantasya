<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

class DrinkOfCreationMessage extends AbstractApplyMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' feels full of energy and zest for action.';
	}
}
