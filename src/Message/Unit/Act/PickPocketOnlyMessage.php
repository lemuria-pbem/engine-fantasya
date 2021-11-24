<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Act;

use Lemuria\Engine\Message;

class PickPocketOnlyMessage extends PickPocketMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' picked ' . $this->silver . ' from the pocket of unit ' . $this->enemy . '.';
	}
}
