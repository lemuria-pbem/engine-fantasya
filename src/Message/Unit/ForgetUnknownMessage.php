<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class ForgetUnknownMessage extends ForgetMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has no knowledge in ' . $this->talent . ' to forget.';
	}
}
