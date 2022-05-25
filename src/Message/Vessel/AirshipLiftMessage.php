<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class AirshipLiftMessage extends AbstractVesselMessage
{
	protected string $level = Message::SUCCESS;

	protected Section $section = Section::MOVEMENT;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' lifts up in the air to sail over the land.';
	}
}
