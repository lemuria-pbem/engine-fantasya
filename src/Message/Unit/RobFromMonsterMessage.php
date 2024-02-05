<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class RobFromMonsterMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Event;

	protected Section $section = Section::Battle;

	protected function create(): string {
		return 'Unit ' . $this->id . ' is assaulted and robbed by monsters.';
	}
}
