<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class SupportHungerMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Production;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is starving.';
	}
}
