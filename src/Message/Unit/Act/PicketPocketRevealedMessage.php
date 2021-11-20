<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

class PicketPocketRevealedMessage extends PicketPocketNothingMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' tried to pick some silver from ' . $this->enemy . ' but was discovered.';
	}
}
