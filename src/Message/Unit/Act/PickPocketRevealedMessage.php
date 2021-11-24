<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

class PickPocketRevealedMessage extends PickPocketNothingMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' tried to pick some silver from ' . $this->enemy . ' but was discovered.';
	}
}
