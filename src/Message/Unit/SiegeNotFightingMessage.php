<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class SiegeNotFightingMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Battle;

	protected function create(): string {
		return 'Unit ' . $this->id . ' will not participate in any siege.';
	}
}
