<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Section;

class TravelNoMoreMessage extends AbstractUnitMessage
{
	protected Section $section = Section::MOVEMENT;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot travel further on the road.';
	}
}
