<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Engine\Message\Result;

class TeachRegionMessage extends TeachStudentMessage
{
	protected Result $result = Result::Failure;

	protected Reliability $reliability = Reliability::Unreliable;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot teach unit ' . $this->student . ': Not in our region.';
	}
}
