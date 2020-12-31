<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Message;

class SortLastMessage extends AbstractUnitMessage
{
	protected string $level = Message::DEBUG;

	protected function create(): string {
		return 'Unit ' . $this->id . ' reordered as last.';
	}
}
