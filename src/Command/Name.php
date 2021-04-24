<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Construction\NameConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Construction\NameOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Party\NameContinentMessage;
use Lemuria\Engine\Fantasya\Message\Party\NameContinentUndoMessage;
use Lemuria\Engine\Fantasya\Message\Party\NamePartyMessage;
use Lemuria\Engine\Fantasya\Message\Region\NameCastleMessage;
use Lemuria\Engine\Fantasya\Message\Region\NameRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameNoContinentMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameNotInConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameNotInVesselMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\NameCaptainMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\NameVesselMessage;
use Lemuria\Model\Fantasya\Building\Castle;
use Lemuria\Model\Fantasya\Construction;

/**
 * The Name command is used to set the name of a unit or the construction, region or vessel it controls.
 *
 * - NAME Partei <Name>
 * - NAME [Einheit] <Name>
 * - NAME Burg|Gebäude <Name>
 * - NAME Region <Name>
 * - NAME Schiff <Name>
 * - NAME Kontinent|Insel <Name>
 */
final class Name extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n <= 0) {
			throw new InvalidCommandException($this, 'No name given.');
		}
		if ($n === 1) {
			$type = 'Einheit';
			$name = $this->trimName($this->phrase->getLine());
		} else {
			$type = $this->phrase->getParameter();
			$name = $this->trimName($this->phrase->getLine(2));
		}

		switch (strtolower($type)) {
			case 'einheit' :
				$this->renameUnit($name);
				break;
			case 'burg' :
			case 'gebäude' :
			case 'gebaeude':
				$this->renameConstruction($name);
				break;
			case 'region' :
				$this->renameRegion($name);
				break;
			case 'schiff' :
				$this->renameVessel($name);
				break;
			case 'partei' :
				$this->renameParty($name);
				break;
			case 'kontinent' :
			case 'insel' :
				$this->setContinentName($name);
				break;
			default :
				$this->renameUnit($this->trimName($this->phrase->getLine()));
		}
	}


	private function renameParty(string $name): void {
		$party = $this->unit->Party();
		$party->setName($name);
		$this->message(NamePartyMessage::class, $party)->p($name);
	}

	private function renameUnit(string $name): void {
		$this->unit->setName($name);
		$this->message(NameUnitMessage::class)->p($name);
	}

	private function renameConstruction(string $name): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			$owner = $construction->Inhabitants()->Owner();
			if ($owner && $owner === $this->unit) {
				$construction->setName($name);
				$this->message(NameConstructionMessage::class, $construction)->p($name);
				return;
			}
			$this->message(NameOwnerMessage::class, $construction)->e($this->unit);
			return;
		}
		$this->message(NameNotInConstructionMessage::class);
	}

	private function renameRegion(string $name): void {
		$region = $this->unit->Region();
		$home   = $this->unit->Construction();
		if ($home) {
			$castle = null; /* @var Construction $castle */
			foreach ($region->Estate() as $construction /* @var Construction $construction */) {
				if ($construction->Building() instanceof Castle) {
					if (!$castle || $construction->Size() >= $castle->Size()) {
						$castle = $construction;
					}
				}
			}
			if ($castle === $home && $home->Inhabitants()->Owner() === $this->unit) {
				$region->setName($name);
				$this->message(NameRegionMessage::class, $region)->p($name);
				return;
			}
		}
		$this->message(NameCastleMessage::class, $region)->e($this->unit);
	}

	private function renameVessel(string $name): void {
		$vessel = $this->unit->Vessel();
		if ($vessel) {
			$captain = $vessel->Passengers()->Owner();
			if ($captain && $captain === $this->unit) {
				$vessel->setName($name);
				$this->message(NameVesselMessage::class, $vessel)->p($name);
				return;
			}
			$this->message(NameCaptainMessage::class, $vessel)->e($this->unit);
			return;
		}
		$this->message(NameNotInVesselMessage::class);
	}

	private function setContinentName(string $name): void {
		$continent = $this->unit->Region()->Continent();
		if ($continent) {
			$party = $this->unit->Party();
			if (empty($name)) {
				$continent->setNameFor($party);
				$this->message(NameContinentUndoMessage::class, $party)->p($continent->Name());
			} else {
				$continent->setNameFor($party, $name);
				$this->message(NameContinentMessage::class, $party)->p($continent->Name())->p($name, NameContinentMessage::NAME);
			}
		} else {
			$this->message(NameNoContinentMessage::class);
		}
	}

	private function trimName(string $name): string {
		return trim($name, "\"'`'^°!§$%&/()=?{[]}\\+*~#<>|,.-;:_ ");
	}
}
