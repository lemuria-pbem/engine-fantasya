<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Vessel;

use Lemuria\Engine\Message;

class TravelShipTooHeavyMessage extends AbstractVesselMessage
{
	protected string $level = Message::FAILURE;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is too heavy to move.';
	}
}
