<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message;

class StealMessage extends StealOnlyMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' steals ' . $this->pickings . ' from unit ' . $this->unit . '.';
	}
}
