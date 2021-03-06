<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class DismissOnlyPeasantsMessage extends DismissPeasantsMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only dismiss ' . $this->persons . ' persons to the peasants of region ' . $this->region . '.';
	}
}
