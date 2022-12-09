<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

abstract class AbstractNoDemandMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Debug;

	protected Section $section = Section::Production;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot ' . $this->createActivity() . ', no demand.';
	}

	abstract protected function createActivity(): string;
}
