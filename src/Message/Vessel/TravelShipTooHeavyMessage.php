<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message;
use Lemuria\Engine\Message\Section;

class TravelShipTooHeavyMessage extends AbstractVesselMessage
{
	protected string $level = Message::FAILURE;

	protected Section $section = Section::MOVEMENT;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is too heavy to move.';
	}
}
