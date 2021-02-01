<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Message\Unit;

use Lemuria\Engine\Lemuria\Message\LemuriaMessage;

class SmashLeaveVesselMessage extends SmashNotVesselOwnerMessage
{
	protected string $level = LemuriaMessage::SUCCESS;

	protected function create(): string {
		return 'Unit ' . $this->id . ' has left the vessel ' . $this->vessel . ' before it is destroyed.';
	}
}
