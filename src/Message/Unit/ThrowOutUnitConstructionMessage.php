<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class ThrowOutUnitConstructionMessage extends ThrowOutOwnMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'Unit ' . $this->unit . ' has been forced to leave the construction.';
	}
}
