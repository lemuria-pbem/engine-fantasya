<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Vessel;

use Lemuria\Engine\Message;

class FounderEffectMessage extends AbstractVesselMessage
{
	protected string $level = Message::EVENT;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' is too heavy and will take damage if excess payload is not thrown overboard.';
	}
}
