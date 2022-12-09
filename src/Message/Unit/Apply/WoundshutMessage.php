<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit\Apply;

use Lemuria\Engine\Fantasya\Message\Unit\AbstractUnitMessage;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class WoundshutMessage extends AbstractUnitMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::MAGIC;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is healing its wounds.';
	}
}
