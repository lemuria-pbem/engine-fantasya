<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
use Lemuria\Engine\Lemuria\Message\Unit\EnterAlreadyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\EnterDeniedMessage;
use Lemuria\Engine\Lemuria\Message\Unit\EnterMessage;
use Lemuria\Engine\Lemuria\Message\Unit\EnterNotFoundMessage;
use Lemuria\Engine\Lemuria\Message\Unit\EnterTooLargeMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LeaveConstructionDebugMessage;
use Lemuria\Engine\Lemuria\Message\Unit\LeaveVesselDebugMessage;
use Lemuria\Id;
use Lemuria\Model\Lemuria\Construction;
use Lemuria\Model\Lemuria\Relation;

/**
 * A unit enters a construction using the Enter command.
 *
 * - BETRETEN <Construction>
 */
final class Enter extends UnitCommand
{
	protected function run(): void {
		if ($this->phrase->count() < 1) {
			throw new UnknownCommandException($this);
		}
		$id = Id::fromId($this->phrase->getParameter());

		$construction = $this->unit->Construction();
		if ($construction && $construction->Id()->Id() === $id->Id()) {
			$this->message(EnterAlreadyMessage::class)->e($construction);
			return;
		}
		if (!$this->unit->Region()->Estate()->has($id)) {
			$this->message(EnterNotFoundMessage::class)->p($id->Id());
			return;
		}
		$newConstruction = Construction::get($id);
		if ($newConstruction->getFreeSpace() < $this->unit->Size()) {
			$this->message(EnterTooLargeMessage::class)->e($newConstruction);
			return ;
		}
		if (!$this->checkPermission($newConstruction)) {
			$this->message(EnterDeniedMessage::class)->e($newConstruction);
			return;
		}

		if ($construction) {
			$construction->Inhabitants()->remove($this->unit);
			$this->message(LeaveConstructionDebugMessage::class)->e($construction);
		} else {
			$vessel = $this->unit->Vessel();
			if ($vessel) {
				$vessel->Passengers()->remove($this->unit);
				$this->message(LeaveVesselDebugMessage::class)->e($vessel);
			}
		}
		$newConstruction->Inhabitants()->add($this->unit);
		$this->message(EnterMessage::class)->e($newConstruction);
	}

	private function checkPermission(Construction $construction): bool {
		$owner = $construction->Inhabitants()->Owner();
		if ($owner) {
			$ownerParty = $owner->Party();
			if ($ownerParty !== $this->unit->Party()) {
				if (!$ownerParty->Diplomacy()->has(Relation::ENTER, $this->unit)) {
					return false;
				}
			}
		}
		return true;
	}
}
