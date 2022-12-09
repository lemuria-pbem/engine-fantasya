<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class RegulateNotInsideMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected Section $section = Section::ECONOMY;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is not inside a market to regulate tradeables.';
	}
}
