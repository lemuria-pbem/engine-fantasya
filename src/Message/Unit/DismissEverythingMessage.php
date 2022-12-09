<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;

class DismissEverythingMessage extends AbstractUnitMessage
{
	protected Result $result = Result::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is dismissed completely.';
	}
}
