<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GrantFromInsideMessage extends GrantFromOutsideMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot take over a foreign command.';
	}
}
