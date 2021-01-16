<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Message\Construction\LeaveNewOwnerMessage;
use Lemuria\Engine\Lemuria\Message\Construction\LeaveNoOwnerMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LeaveConstructionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LeaveNotMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LeaveVesselMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\LeaveNewCaptainMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\LeaveNoCaptainMessage;

/**
 * A unit leaves the construction or vessel using the Leave command.
 *
 * - VERLASSEN
 */
final class Leave extends UnitCommand
{
	protected function run(): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			$construction->Inhabitants()->remove($this->unit);
			$this->message(LeaveConstructionMessage::class)->e($construction);
			$newOwner = $construction->Inhabitants()->Owner();
			if ($newOwner) {
				$this->message(LeaveNewOwnerMessage::class)->e($newOwner);
			} else {
				$this->message(LeaveNoOwnerMessage::class);
			}
		} else {
			$vessel = $this->unit->Vessel();
			if ($vessel) {
				$vessel->Passengers()->remove($this->unit);
				$this->message(LeaveVesselMessage::class)->e($vessel);
				$newCaptain = $vessel->Passengers()->Owner();
				if ($newCaptain) {
					$this->message(LeaveNewCaptainMessage::class)->e($newCaptain);
				} else {
					$this->message(LeaveNoCaptainMessage::class);
				}
			} else {
				$this->message(LeaveNotMessage::class);
			}
		}
	}
}
