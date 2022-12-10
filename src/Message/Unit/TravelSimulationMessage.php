<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Unit;

use Lemuria\Engine\Fantasya\Message\Reliability;
use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class TravelSimulationMessage extends AbstractUnitMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Movement;

	protected Reliability $reliability = Reliability::Unreliable;

	protected function create(): string {
		return 'Unit ' . $this->id . ' cannot move further in this simulation.';
	}
}
