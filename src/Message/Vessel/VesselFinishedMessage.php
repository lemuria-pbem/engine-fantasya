<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Message\Vessel;

use Lemuria\Engine\Message;

class VesselFinishedMessage extends AbstractVesselMessage
{
	protected string $level = Message::SUCCESS;

	protected function create(): string {
		return 'Vessel ' . $this->id . ' has been finished.';
	}
}
