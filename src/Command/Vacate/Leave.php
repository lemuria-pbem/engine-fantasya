<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Vacate;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Construction\LeaveNewOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Construction\LeaveNoOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveVesselMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\LeaveNewCaptainMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\LeaveNoCaptainMessage;

/**
 * A unit leaves the construction or vessel using the Leave command.
 *
 * - VERLASSEN
 */
final class Leave extends UnitCommand
{
	use SiegeTrait;

	protected function run(): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			if ($this->initSiege($construction)->canEnterOrLeave($this->unit)) {
				$construction->Inhabitants()->remove($this->unit);
				$this->message(LeaveConstructionMessage::class)->e($construction);
				$newOwner = $construction->Inhabitants()->Owner();
				if ($newOwner) {
					$this->message(LeaveNewOwnerMessage::class)->e($newOwner);
				} else {
					$this->message(LeaveNoOwnerMessage::class);
				}
			} else {
				$this->message(LeaveSiegeMessage::class);
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
