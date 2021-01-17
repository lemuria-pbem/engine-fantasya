<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class DefaultUnknownMessage extends DefaultInvalidMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot add an unknown command to defaults: ' . $this->command;
	}
}
