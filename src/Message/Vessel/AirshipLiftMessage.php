<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class AirshipLiftMessage extends AbstractVesselMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Movement;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' lifts up in the air to sail over the land.';
	}
}
