<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

class FeeNotInsideMessage extends FeeNotOwnerMessage
{
	protected function create(): string {
		return 'Unit ' . $this->id . ' must be inside a construction to set a fee.';
	}
}
