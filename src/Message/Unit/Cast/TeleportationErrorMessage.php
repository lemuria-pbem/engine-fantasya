<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Cast;

use Lemuria\Engine\Message;

class TeleportationErrorMessage extends TeleportationForeignMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teleport foreign unit ' . $this->unit . ' - its treasury is too heavy.';
	}
}
