<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class ForgetLowerMessage extends ForgetLevelMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot forget more knowledge in ' . $this->talent . ', it already has level ' . $this->ability . '.';
	}
}
