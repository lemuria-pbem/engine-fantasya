<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Trespass;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Message\Unit\BoardAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BoardDeniedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveConstructionDebugMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\LeaveVesselDebugMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BoardMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BoardNotFoundMessage;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Vessel;

/**
 * A unit enters a vessel using the Board command.
 *
 * - BESTEIGEN <Vessel>
 */
final class Board extends UnitCommand
{
	use SiegeTrait;

	protected function run(): void {
		if ($this->phrase->count() < 1) {
			throw new InvalidCommandException($this);
		}
		$id = Id::fromId($this->phrase->getParameter(0));

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
				if ($this->initSiege($construction)->canEnterOrLeave($this->unit)) {
					$construction->Inhabitants()->remove($this->unit);
					$this->message(LeaveConstructionDebugMessage::class)->e($construction);
				} else {
					$this->message(LeaveSiegeMessage::class);
					return;
				}
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
				if ($this->context->getTurnOptions()->IsSimulation() || !$captainParty->Diplomacy()->has(Relation::ENTER, $this->unit)) {
					return false;
				}
			}
		}
		return true;
	}
}
