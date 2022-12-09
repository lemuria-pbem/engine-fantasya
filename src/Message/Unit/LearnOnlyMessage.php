<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class LearnOnlyMessage extends LearnSilverMessage
{
	protected Result $result = Result::FAILURE;

	protected function create(): string {
		return 'Unit ' . $this->id . ' can only pay ' . $this->silver . ' silver to learn ' . $this->talent . '.';
	}
}
