<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class LearnFleetMessage extends LearnProgressMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is partly occupied with realm transport and can learn ' . $this->talent . ' with ' . $this->experience . ' experience only.';
	}
}
