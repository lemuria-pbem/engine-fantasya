<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class NumberRealmMessage extends NumberRealmUsedMessage
{
	protected Result $result = Result::Success;

	protected function create(): string {
		return 'New ID of realm ' . $this->realm . ' is ' . $this->newId . '.';
	}
}
