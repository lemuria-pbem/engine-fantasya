<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class TeachEmptyMessage extends TeachStudentMessage
{
	protected Result $result = Result::Failure;

	protected function create(): string {
		return 'Unit ' . $this->student . ' has no students.';
	}
}
