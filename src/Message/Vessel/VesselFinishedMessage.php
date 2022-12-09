<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class VesselFinishedMessage extends AbstractVesselMessage
{
	protected Result $result = Result::Success;

	protected Section $section = Section::Production;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' has been finished.';
	}
}
