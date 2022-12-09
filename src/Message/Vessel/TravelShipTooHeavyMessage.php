<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class TravelShipTooHeavyMessage extends AbstractVesselMessage
{
	protected Result $result = Result::Failure;

	protected Section $section = Section::Movement;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is too heavy to move.';
	}
}
