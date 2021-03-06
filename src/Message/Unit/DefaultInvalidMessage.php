<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\LemuriaMessage;

class DefaultInvalidMessage extends DefaultMessage
{
	protected string $level = LemuriaMessage::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot add invalid default: ' . $this->command;
	}
}
