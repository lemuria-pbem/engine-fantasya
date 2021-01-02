<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

class GrantNothingMessage extends GrantFromOutsideMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot grant a command it does not hold.';
	}
}
