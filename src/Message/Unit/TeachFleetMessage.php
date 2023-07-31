<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class TeachFleetMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Study;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is partly occupied with realm transport and can only teach less than normal.';
	}
}
