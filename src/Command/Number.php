<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Message\Construction\NumberMessage as ConstructionNumberMessage;
use Lemuria\Engine\Lemuria\Message\Construction\NumberOwnerMessage;
use Lemuria\Engine\Lemuria\Message\Construction\NumberUsedMessage as ConstructionNumberUsedMessage;
use Lemuria\Engine\Lemuria\Message\Unit\NumberMessage as UnitNumberMessage;
use Lemuria\Engine\Lemuria\Message\Unit\NumberNotInConstructionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\NumberNotInVesselMessage;
use Lemuria\Engine\Lemuria\Message\Unit\NumberUsedMessage as UnitNumberUsedMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\NumberCaptainMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\NumberMessage as VesselNumberMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\NumberUsedMessage as VesselNumberUsedMessage;
use Lemuria\Model\Catalog;
use Lemuria\Engine\Lemuria\Exception\CommandException;
use Lemuria\Engine\Lemuria\Exception\UnknownCommandException;
use Lemuria\Exception\IdException;
use Lemuria\Id;
use Lemuria\Lemuria;

/**
 * The Number command is used to set the ID of a unit, its party, or a construction or vessel it controls.
 *
 * - NUMMER [Einheit] <ID>
 * - NUMMER Partei <ID>
 * - NUMMER Burg|Gebäude <ID>
 * - NUMMER Schiff <ID>
 */
final class Number extends UnitCommand {

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n <= 0) {
			throw new CommandException('No ID given.');
		}
		if ($n === 1) {
			$type = 'Einheit';
			$id   = $this->phrase->getParameter();
		} else {
			$type = $this->phrase->getParameter(1);
			$id   = $this->phrase->getParameter(2);
		}
		try {
			$newId = Id::fromId($id);
		} catch (IdException $e) {
			throw new CommandException('Invalid ID given.', 0, $e);
		}

		switch (strtolower($type)) {
			case 'einheit' :
				$this->setUnitId($newId);
				break;
			case 'burg' :
			case 'gebäude' :
			case 'gebaeude':
				$this->setConstructionId($newId);
				break;
			case 'schiff' :
				$this->setVesselId($newId);
				break;
			default :
				throw new UnknownCommandException($this);
		}
	}

	/**
	 * Set name of unit.
	 *
	 * @param Id $id
	 */
	private function setUnitId(Id $id): void {
		if (Lemuria::Catalog()->has($id, Catalog::UNITS)) {
			$this->message(UnitNumberUsedMessage::class)->p($id->Id());
			return;
		}

		$oldId        = $this->unit->Id()->Id();
		$party        = $this->unit->Party();
		$region       = $this->unit->Region();
		$construction = $this->unit->Construction();
		$inhabitants  = $construction ? $construction->Inhabitants() : null;
		$isOwner      = $inhabitants ? $inhabitants->Owner() === $this->unit : false;
		if ($inhabitants) {
			$inhabitants->remove($this->unit);
		}
		$region->Residents()->remove($this->unit);
		$party->People()->remove($this->unit);

		$this->unit->setId($id);
		$party->People()->add($this->unit);
		$region->Residents()->add($this->unit);
		if ($inhabitants) {
			$inhabitants->add($this->unit);
			if ($isOwner) {
				$inhabitants->setOwner($this->unit);
			}
		}
		$this->message(UnitNumberMessage::class)->p($oldId);
	}

	/**
	 * Set name of construction the unit controls.
	 *
	 * @param Id $id
	 */
	private function setConstructionId(Id $id): void {
		$construction = $this->unit->Construction();
		if (!$construction) {
			$this->message(NumberNotInConstructionMessage::class);
			return;
		}
		if (Lemuria::Catalog()->has($id, Catalog::CONSTRUCTIONS)) {
			$this->message(ConstructionNumberUsedMessage::class)->e($construction)->p($id->Id());
			return;
		}
		if ($construction->Inhabitants()->Owner() !== $this->unit) {
			$this->message(NumberOwnerMessage::class)->e($construction)->e($this->unit, NumberOwnerMessage::OWNER);
			return;
		}

		$oldId  = $construction->Id()->Id();
		$region = $construction->Region();
		$estate = $region->Estate();
		$estate->remove($construction);

		$construction->setId($id);
		$estate->add($construction);
		$this->message(ConstructionNumberMessage::class)->e($construction)->p($oldId);
	}

	/**
	 * Set name of vessel the unit controls.
	 *
	 * @param Id $id
	 */
	private function setVesselId(Id $id): void {
		$vessel = $this->unit->Vessel();
		if (!$vessel) {
			$this->message(NumberNotInVesselMessage::class);
			return;
		}
		if (Lemuria::Catalog()->has($id, Catalog::VESSELS)) {
			$this->message(VesselNumberUsedMessage::class)->e($vessel)->p($id->Id());
			return;
		}
		if ($vessel->Passengers()->Owner() !== $this->unit) {
			$this->message(NumberCaptainMessage::class)->e($vessel)->e($this->unit, NumberCaptainMessage::CAPTAIN);
			return;
		}

		$oldId  = $vessel->Id()->Id();
		$region = $vessel->Region();
		$fleet  = $region->Fleet();
		$fleet->remove($vessel);

		$vessel->setId($id);
		$fleet->add($vessel);
		$this->message(VesselNumberMessage::class)->e($vessel)->p($oldId);
	}
}
