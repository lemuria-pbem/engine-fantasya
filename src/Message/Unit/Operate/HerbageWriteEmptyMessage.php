<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Operate;

use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class HerbageWriteEmptyMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected Section $section = Section::PRODUCTION;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has not explored any herb occurrences to write to the almanac.';
	}
}
