<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Construction\NumberConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Construction\NumberOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Construction\NumberConstructionUsedMessage;
use Lemuria\Engine\Fantasya\Message\Party\NumberPartyMessage;
use Lemuria\Engine\Fantasya\Message\Party\NumberPartyUsedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NumberNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NumberUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NumberUnicumUsedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NumberUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NumberNotInConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NumberNotInVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NumberUnitUsedMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\NumberCaptainMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\NumberVesselMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\NumberVesselUsedMessage;
use Lemuria\Exception\IdException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Unicum;

/**
 * The Number command is used to set the ID of a unit, its party, a construction or vessel it controls, or an unicum it
 * possesses.
 *
 * - NUMMER [Einheit] <ID>
 * - NUMMER Partei <ID>
 * - NUMMER Burg|Gebäude <ID>
 * - NUMMER Schiff <ID>
 * - NUMMER Gegenstand <old ID> <new ID>
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
			$type = $this->phrase->getParameter();
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
			case 'gegenstand' :
				if ($n < 3) {
					throw new InvalidCommandException($this, 'No new ID given');
				}
				$this->setUnicumId($newId);
				break;
			default :
				throw new InvalidCommandException($this, 'Invalid type "' . $type . '".');
		}
	}

	private function setUnitId(Id $id): void {
		if (Lemuria::Catalog()->has($id, Domain::UNIT)) {
			$this->message(NumberUnitUsedMessage::class)->p($id->Id());
			return;
		}

		$oldId = $this->unit->Id();
		Lemuria::Catalog()->reassign($this->unit->replaceId($id), $oldId);
		$this->message(NumberUnitMessage::class)->p($oldId->Id());
	}

	private function setConstructionId(Id $id): void {
		$construction = $this->unit->Construction();
		if (!$construction) {
			$this->message(NumberNotInConstructionMessage::class);
			return;
		}
		if (Lemuria::Catalog()->has($id, Domain::CONSTRUCTION)) {
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
		Lemuria::Catalog()->reassign($construction, $oldId);
		$this->message(NumberConstructionMessage::class, $construction)->p($oldId->Id());
	}

	private function setVesselId(Id $id): void {
		$vessel = $this->unit->Vessel();
		if (!$vessel) {
			$this->message(NumberNotInVesselMessage::class);
			return;
		}
		if (Lemuria::Catalog()->has($id, Domain::VESSEL)) {
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
		Lemuria::Catalog()->reassign($vessel, $oldId);
		$this->message(NumberVesselMessage::class, $vessel)->p($oldId->Id());
	}

	private function setPartyId(Id $id): void {
		$party = $this->unit->Party();
		if (Lemuria::Catalog()->has($id, Domain::PARTY)) {
			$this->message(NumberPartyUsedMessage::class, $party)->p($id->Id());
			return;
		}

		$oldId = $party->Id();
		Lemuria::Catalog()->reassign($party->setId($id), $oldId);
		$this->message(NumberPartyMessage::class, $party)->p($oldId->Id());
	}

	private function setUnicumId(Id $oldId): void {
		$treasury = $this->unit->Treasury();
		if (!$treasury->has($oldId)) {
			$this->message(NumberNoUnicumMessage::class)->p($oldId->Id());
			return;
		}
		$newId = Id::fromId($this->phrase->getParameter(3));
		if (Lemuria::Catalog()->has($newId, Domain::UNICUM)) {
			$this->message(NumberUnicumUsedMessage::class)->p($newId->Id());
			return;
		}
		/** @var Unicum $unicum */
		$unicum      = $treasury[$oldId->Id()];
		$composition = $unicum->Composition();
		Lemuria::Catalog()->reassign($unicum->replaceId($newId), $oldId);
		$this->message(NumberUnicumMessage::class)->s($composition)->p($oldId->Id())->p($newId->Id(), NumberUnicumUsedMessage::NEW_ID);
	}
}
