<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
use Lemuria\Engine\Lemuria\Message\Unit\BoardAlreadyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\BoardDeniedMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LeaveConstructionDebugMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LeaveVesselDebugMessage;
use Lemuria\Engine\Lemuria\Message\Unit\BoardMessage;
use Lemuria\Engine\Lemuria\Message\Unit\BoardNotFoundMessage;
use Lemuria\Engine\Lemuria\Message\Unit\BoardTooHeavyMessage;
use Lemuria\Id;
use Lemuria\Model\Lemuria\Relation;
use Lemuria\Model\Lemuria\Vessel;

/**
 * A unit enters a vessel using the Board command.
 *
 * - BESTEIGEN <Vessel>
 */
final class Board extends UnitCommand
{
	protected function run(): void {
		if ($this->phrase->count() < 1) {
			throw new UnknownCommandException($this);
		}
		$id = Id::fromId($this->phrase->getParameter());

		$vessel = $this->unit->Vessel();
		if ($vessel && $vessel->Id()->Id() === $id->Id()) {
			$this->message(BoardAlreadyMessage::class)->e($vessel);
			return;
		}
		if (!$this->unit->Region()->Fleet()->has($id)) {
			$this->message(BoardNotFoundMessage::class)->p($id->Id());
			return;
		}

		$newVessel = Vessel::get($id);
		if ($newVessel->Space() < $this->unit->Weight()) {
			$this->message(BoardTooHeavyMessage::class)->e($newVessel);
			return;
		}
		if (!$this->checkPermission($newVessel)) {
			$this->message(BoardDeniedMessage::class)->e($newVessel);
			return;
		}

		if ($vessel) {
			$vessel->Passengers()->remove($this->unit);
			$this->message(LeaveVesselDebugMessage::class)->e($vessel);
		} else {
			$construction = $this->unit->Construction();
			if ($construction) {
				$construction->Inhabitants()->remove($this->unit);
				$this->message(LeaveConstructionDebugMessage::class)->e($construction);
			}
		}
		$newVessel->Passengers()->add($this->unit);
		$this->message(BoardMessage::class)->e($newVessel);
	}

	private function checkPermission(Vessel $vessel): bool {
		$captain = $vessel->Passengers()->Owner();
		if ($captain) {
			$captainParty = $captain->Party();
			if ($captainParty !== $this->unit->Party()) {
				if (!$captainParty->Diplomacy()->has(Relation::ENTER, $this->unit)) {
					return false;
				}
			}
		}
		return true;
	}
}
