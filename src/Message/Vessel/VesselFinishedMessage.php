<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message\Result;
use Lemuria\Engine\Message\Section;

class VesselFinishedMessage extends AbstractVesselMessage
{
	protected Result $result = Result::SUCCESS;

	protected Section $section = Section::PRODUCTION;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' has been finished.';
	}
}
