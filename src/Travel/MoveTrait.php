<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Travel;

use Lemuria\Engine\Fantasya\Message\Construction\LeaveNewOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Construction\LeaveNoOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveConstructionDebugMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveVesselDebugMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TravelGuardCancelMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\LeaveNewCaptainMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\LeaveNoCaptainMessage;
use Lemuria\Model\Fantasya\Unit;

trait MoveTrait
{
	protected function clearUnitStatus(Unit $unit): void {
		if ($unit->IsGuarding()) {
			$unit->setIsGuarding(false);
			$this->message(TravelGuardCancelMessage::class, $unit);
		}
	}

	protected function clearConstructionOwner(Unit $unit): void {
		$construction = $unit->Construction();
		if ($construction) {
			$inhabitants = $construction->Inhabitants();
			$isOwner     = $inhabitants->Owner() === $unit;
			$inhabitants->remove($unit);
			$this->message(LeaveConstructionDebugMessage::class, $unit)->e($construction);
			if ($isOwner) {
				$owner = $inhabitants->Owner();
				if ($owner) {
					$this->message(LeaveNewOwnerMessage::class, $construction)->e($owner);
				} else {
					$this->message(LeaveNoOwnerMessage::class, $construction);
				}
			}
		}
	}

	protected function clearVesselCaptain(Unit $unit): void {
		$vessel = $unit->Vessel();
		if ($vessel) {
			$passenger = $vessel->Passengers();
			$isCaptain = $passenger->Owner() === $unit;
			$passenger->remove($unit);
			$this->message(LeaveVesselDebugMessage::class, $unit)->e($vessel);
			if ($isCaptain) {
				$captain = $passenger->Owner();
				if ($captain) {
					$this->message(LeaveNewCaptainMessage::class, $vessel)->e($captain);
				} else {
					$this->message(LeaveNoCaptainMessage::class, $vessel);
				}
			}
		}
	}
}
