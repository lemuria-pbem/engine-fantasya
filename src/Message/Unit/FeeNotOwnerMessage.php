<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class FeeNotOwnerMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Economy;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is not the market owner and cannot set the fee.';
	}
}
