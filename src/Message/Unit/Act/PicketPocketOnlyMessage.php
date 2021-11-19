<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Message;

class PicketPocketOnlyMessage extends PicketPocketMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' picked ' . $this->silver . ' from the pocket of unit ' . $this->enemy . '.';
	}
}
