<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GrantAlreadyMessage extends GrantFromOutsideMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' already has the command.';
	}
}
