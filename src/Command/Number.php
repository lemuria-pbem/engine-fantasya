<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Message\Construction\NumberConstructionMessage;
use Lemuria\Engine\Lemuria\Message\Construction\NumberOwnerMessage;
use Lemuria\Engine\Lemuria\Message\Construction\NumberConstructionUsedMessage;
use Lemuria\Engine\Lemuria\Message\Party\NumberPartyMessage;
use Lemuria\Engine\Lemuria\Message\Party\NumberPartyUsedMessage;
use Lemuria\Engine\Lemuria\Message\Unit\NumberUnitMessage;
use Lemuria\Engine\Lemuria\Message\Unit\NumberNotInConstructionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\NumberNotInVesselMessage;
use Lemuria\Engine\Lemuria\Message\Unit\NumberUnitUsedMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\NumberCaptainMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\NumberVesselMessage;
use Lemuria\Engine\Lemuria\Message\Vessel\NumberVesselUsedMessage;
use Lemuria\Model\Catalog;
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
final class Number extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n <= 0) {
			throw new InvalidCommandException($this, 'No ID given.');
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
			throw new InvalidCommandException($this, 'Invalid ID given.', $e);
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
			case 'partei' :
				$this->setPartyId($newId);
				break;
			default :
				throw new InvalidCommandException($this, 'Invalid type "' . $type . '".');
		}
	}

	private function setUnitId(Id $id): void {
		if (Lemuria::Catalog()->has($id, Catalog::UNITS)) {
			$this->message(NumberUnitUsedMessage::class)->p($id->Id());
			return;
		}

		$oldId = $this->unit->Id();
		Lemuria::Catalog()->reassign($oldId, $this->unit->replaceId($id));
		$this->message(NumberUnitMessage::class)->p($oldId->Id());
	}

	private function setConstructionId(Id $id): void {
		$construction = $this->unit->Construction();
		if (!$construction) {
			$this->message(NumberNotInConstructionMessage::class);
			return;
		}
		if (Lemuria::Catalog()->has($id, Catalog::CONSTRUCTIONS)) {
			$this->message(NumberConstructionUsedMessage::class, $construction)->p($id->Id());
			return;
		}
		if ($construction->Inhabitants()->Owner() !== $this->unit) {
			$this->message(NumberOwnerMessage::class, $construction)->e($this->unit);
			return;
		}

		$oldId = $construction->Id();
		$construction->setId($id);
		$construction->Region()->Estate()->replace($oldId, $id);
		Lemuria::Catalog()->reassign($oldId, $construction);
		$this->message(NumberConstructionMessage::class, $construction)->p($oldId->Id());
	}

	private function setVesselId(Id $id): void {
		$vessel = $this->unit->Vessel();
		if (!$vessel) {
			$this->message(NumberNotInVesselMessage::class);
			return;
		}
		if (Lemuria::Catalog()->has($id, Catalog::VESSELS)) {
			$this->message(NumberVesselUsedMessage::class, $vessel)->p($id->Id());
			return;
		}
		if ($vessel->Passengers()->Owner() !== $this->unit) {
			$this->message(NumberCaptainMessage::class, $vessel)->e($this->unit);
			return;
		}

		$oldId = $vessel->Id();
		$vessel->setId($id);
		$vessel->Region()->Fleet()->replace($oldId, $id);
		Lemuria::Catalog()->reassign($oldId, $vessel);
		$this->message(NumberVesselMessage::class, $vessel)->p($oldId->Id());
	}

	private function setPartyId(Id $id): void {
		$party = $this->unit->Party();
		if (Lemuria::Catalog()->has($id, Catalog::PARTIES)) {
			$this->message(NumberPartyUsedMessage::class, $party)->p($id->Id());
			return;
		}

		$oldId = $party->Id();
		Lemuria::Catalog()->reassign($oldId, $party->setId($id));
		$this->message(NumberPartyMessage::class, $party)->p($oldId->Id());
	}
}
