<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class GrantNoConstructionMessage extends GrantFromOutsideMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' is not inside a construction to take over.';
	}
}
