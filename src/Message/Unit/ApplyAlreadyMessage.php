<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class ApplyAlreadyMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Magic;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot apply another potion.';
	}
}
