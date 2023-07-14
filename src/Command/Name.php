<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Message\Casus;
use Lemuria\Engine\Fantasya\Message\Construction\NameConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Construction\NameOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Party\NameContinentMessage;
use Lemuria\Engine\Fantasya\Message\Party\NameContinentUndoMessage;
use Lemuria\Engine\Fantasya\Message\Party\NamePartyMessage;
use Lemuria\Engine\Fantasya\Message\Region\NameCastleMessage;
use Lemuria\Engine\Fantasya\Message\Region\NameRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameMonumentOnceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameNoContinentMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameRealmCentralMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameRealmMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameRealmNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameNotInConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\NameNotInVesselMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\NameCaptainMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\NameVesselMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Building\Castle;
use Lemuria\Model\Fantasya\Building\Monument;
use Lemuria\Model\Reassignment;

/**
 * The Name command is used to set the name of a unit, an unicum it possesses or the construction, region or vessel it
 * controls.
 *
 * - NAME Partei <Name>
 * - NAME [Einheit] <Name>
 * - NAME Burg|Gebäude <Name>
 * - NAME Region <Name>
 * - NAME Reich <Name>
 * - NAME Schiff <Name>
 * - NAME Kontinent|Insel <Name>
 * - NAME Gegenstand <ID> <Name>
 */
final class Name extends UnitCommand implements Reassignment
{
	use GrammarTrait;
	use ReassignTrait;

	private const UNICUM = 'gegenstand';

	public static function trimName(string $name): string {
		$name = ltrim($name, "\"'`^°!$%&/()=?{[]}\\+~#<>|,.-;:_ ");
		return rtrim($name, "\"'`^°§$&/(={[]}\\~#<>|,-;:_ ");
	}

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n <= 0) {
			throw new InvalidCommandException($this, 'No name given.');
		}
		if ($n === 1) {
			$type = 'Einheit';
			$name = self::trimName($this->phrase->getLine());
		} else {
			$type = $this->phrase->getParameter();
			$name = self::trimName($this->phrase->getLine(2));
		}

		switch (mb_strtolower($type)) {
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
			case 'reich' :
				$this->renameRealm($name);
				break;
			case 'partei' :
				$this->renameParty($name);
				break;
			case 'kontinent' :
			case 'insel' :
				$this->setContinentName($name);
				break;
			case self::UNICUM :
				if ($n < 3) {
					throw new InvalidCommandException('No name given.');
				}
				$this->renameUnicum($this->phrase->getParameter(2), $this->phrase->getLine(3));
				break;
			default :
				$this->renameUnit(self::trimName($this->phrase->getLine()));
		}
	}

	protected function checkSize(): bool {
		return true;
	}

	protected function checkReassignmentDomain(Domain $domain): bool {
		return $domain === Domain::Unicum;
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		if (strtolower($this->phrase->getParameter()) === self::UNICUM) {
			return $this->getReassignPhraseForParameter(2, $old, $new);
		}
		return null;
	}

	private function renameParty(string $name): void {
		$party = $this->unit->Party();
		if (empty($name)) {
			$name = 'Partei ' . $party->Id();
		}
		$party->setName($name);
		$this->message(NamePartyMessage::class, $party)->p($name);
	}

	private function renameUnit(string $name): void {
		if (empty($name)) {
			$name = 'Einheit ' . $this->unit->Id();
		}
		$this->unit->setName($name);
		$this->message(NameUnitMessage::class)->p($name);
	}

	private function renameConstruction(string $name): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			if ($construction->Building() instanceof Monument) {
				if ($name === 'Monument ' . $construction->Id()) {
					$this->message(NameMonumentOnceMessage::class);
					return;
				}
			}

			$owner = $construction->Inhabitants()->Owner();
			if ($owner && $owner->Party() === $this->unit->Party()) {
				if (empty($name)) {
					$name = $this->translateSingleton($construction->Building(), casus: Casus::Nominative) . ' ' . $construction->Id();
				}
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
		if (empty($name)) {
			$name = $this->translateSingleton($region->Landscape(), casus: Casus::Nominative) . ' ' . $region->Id();
		}
		$estate = $region->Estate();
		if ($estate->isEmpty()) {
			$region->setName($name);
			$this->message(NameRegionMessage::class, $region)->p($name);
			return;
		}

		$home = $this->unit->Construction();
		if ($home) {
			$castle = null;
			foreach ($estate as $construction) {
				if ($construction->Building() instanceof Castle) {
					if (!$castle || $construction->Size() >= $castle->Size()) {
						$castle = $construction;
					}
				}
			}
			if ($castle === $home && $home->Inhabitants()->Owner()->Party() === $this->unit->Party()) {
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
			if ($captain && $captain->Party() === $this->unit->Party()) {
				if (empty($name)) {
					$name = $this->translateSingleton($vessel->Ship(), casus: Casus::Nominative) . ' ' . $vessel->Id();
				}
				$vessel->setName($name);
				$this->message(NameVesselMessage::class, $vessel)->p($name);
				return;
			}
			$this->message(NameCaptainMessage::class, $vessel)->e($this->unit);
			return;
		}
		$this->message(NameNotInVesselMessage::class);
	}

	private function renameUnicum(string $id, string $name): void {
		$treasury = $this->unit->Treasury();
		$id       = $this->toId($id);
		if ($treasury->has($id)) {
			$unicum = $treasury[$id];
			if (empty($name)) {
				$name = $this->translateSingleton($unicum->Composition(), casus: Casus::Nominative) . ' ' . $unicum->Id();
			}
			$unicum->setName($name);
			$this->message(NameUnicumMessage::class)->e($unicum)->p($name);
		} else {
			$this->message(NameNoUnicumMessage::class)->p((string)$id);
		}
	}

	private function renameRealm(string $name): void {
		$region = $this->unit->Region();
		$realm  = $region->Realm();
		if ($realm) {
			$possessions = $this->unit->Party()->Possessions();
			if ($possessions->has($realm->Identifier())) {
				$possession = $possessions[$realm->Identifier()];
				if ($possession === $realm) {
					if ($realm->Territory()->Central() === $region) {
						$realm->setName($name);
						$this->message(NameRealmMessage::class)->p($name);
					} else {
						$this->message(NameRealmCentralMessage::class)->p($realm->Name());
					}
					return;
				}
			}
		}
		$this->message(NameRealmNotFoundMessage::class);
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
}
