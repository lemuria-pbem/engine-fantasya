<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class TravelSimulationMessage extends AbstractUnitMessage
{
	protected Result $result = Result::FAILURE;

	protected Section $section = Section::MOVEMENT;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot move further in this simulation.';
	}
}
