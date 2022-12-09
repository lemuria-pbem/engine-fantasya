<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class CancelMessage extends CancelNoneMessage
{
	protected Result $result = Result::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cancels the trade ' . $this->trade . '.';
	}
}
