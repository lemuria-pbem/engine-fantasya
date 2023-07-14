<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Message\Construction\DescribeConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Construction\DescribeOwnerMessage;
use Lemuria\Engine\Fantasya\Message\Party\DescribeContinentMessage;
use Lemuria\Engine\Fantasya\Message\Party\DescribeContinentUndoMessage;
use Lemuria\Engine\Fantasya\Message\Party\DescribePartyMessage;
use Lemuria\Engine\Fantasya\Message\Region\DescribeCastleMessage;
use Lemuria\Engine\Fantasya\Message\Region\DescribeRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DescribeMonumentOnceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DescribeNoContinentMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DescribeNoUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DescribeRealmCentralMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DescribeRealmMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DescribeRealmNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DescribeUnicumMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DescribeUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DescribeNotInConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DescribeNotInVesselMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\DescribeCaptainMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\DescribeVesselMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Building\Castle;
use Lemuria\Model\Fantasya\Building\Monument;
use Lemuria\Model\Reassignment;

/**
 * The Describe command is used to set the description of a unit, an unicum it possesses or the construction, region or
 * vessel it controls.
 *
 * - BESCHREIBUNG Partei <Beschreibung>
 * - BESCHREIBUNG [Einheit] <Beschreibung>
 * - BESCHREIBUNG Burg|Gebäude <Beschreibung>
 * - BESCHREIBUNG Region <Beschreibung>
 * - BESCHREIBUNG Reich <Beschreibung>
 * - BESCHREIBUNG Schiff <Beschreibung>
 * - BESCHREIBUNG Kontinent|Insel <Beschreibung>
 * - BESCHREIBUNG Gegenstand <ID> <Beschreibung>
 */
final class Describe extends UnitCommand implements Reassignment
{
	use ReassignTrait;

	private const UNICUM = 'gegenstand';

	public static function trimDescription(string $description): string {
		return trim($description, "\"'`^°§$%&/()={[]}\\+*~#<>|,-;:_ ");
	}

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n <= 0) {
			throw new InvalidCommandException($this, 'No description given.');
		}
		if ($n === 1) {
			$type        = 'Einheit';
			$description = self::trimDescription($this->phrase->getParameter());
		} else {
			$type        = $this->phrase->getParameter();
			$description = self::trimDescription($this->phrase->getLine(2));
		}

		switch (mb_strtolower($type)) {
			case 'einheit' :
				$this->describeUnit($description);
				break;
			case 'burg' :
			case 'gebäude' :
			case 'gebaeude':
				$this->describeConstruction($description);
				break;
			case 'region' :
				$this->describeRegion($description);
				break;
			case 'schiff' :
				$this->describeVessel($description);
				break;
			case 'reich' :
				$this->describeRealm($description);
				break;
			case 'partei' :
				$this->describeParty($description);
				break;
			case 'kontinent' :
			case 'insel' :
				$this->setContinentDescription($description);
				break;
			case self::UNICUM :
				if ($n < 3) {
					throw new InvalidCommandException('No description given.');
				}
				$this->describeUnicum($this->phrase->getParameter(2), $this->phrase->getLine(3));
				break;
			default :
				$this->describeUnit(self::trimDescription($this->phrase->getLine()));
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

	private function describeParty(string $description): void {
		$party = $this->unit->Party();
		$party->setDescription($description);
		$this->message(DescribePartyMessage::class, $party);
	}

	private function describeUnit(string $description): void {
		$this->unit->setDescription($description);
		$this->message(DescribeUnitMessage::class);
	}

	private function describeConstruction(string $description): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			if ($construction->Building() instanceof Monument) {
				if ($construction->Description()) {
					$this->message(DescribeMonumentOnceMessage::class);
					return;
				}
			}

			$owner = $construction->Inhabitants()->Owner();
			if ($owner && $owner->Party() === $this->unit->Party()) {
				$construction->setDescription($description);
				$this->message(DescribeConstructionMessage::class)->e($construction);
				return;
			}
			$this->message(DescribeOwnerMessage::class)->setAssignee($construction)->e($this->unit);
			return;
		}
		$this->message(DescribeNotInConstructionMessage::class);
	}

	private function describeRegion(string $description): void {
		$region = $this->unit->Region();
		$estate = $region->Estate();
		if ($estate->isEmpty()) {
			$region->setDescription($description);
			$this->message(DescribeRegionMessage::class)->e($region);
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
				$region->setDescription($description);
				$this->message(DescribeRegionMessage::class)->e($region);
				return;
			}
		}
		$this->message(DescribeCastleMessage::class)->setAssignee($region)->e($this->unit);
	}

	private function describeVessel(string $description): void {
		$vessel = $this->unit->Vessel();
		if ($vessel) {
			$captain = $vessel->Passengers()->Owner();
			if ($captain && $captain->Party() === $this->unit->Party()) {
				$vessel->setDescription($description);
				$this->message(DescribeVesselMessage::class)->e($vessel);
				return;
			}
			$this->message(DescribeCaptainMessage::class)->setAssignee($vessel)->e($this->unit);
			return;
		}
		$this->message(DescribeNotInVesselMessage::class);
	}

	private function describeUnicum(string $id, string $description): void {
		$treasury = $this->unit->Treasury();
		$id       = $this->toId($id);
		if ($treasury->has($id)) {
			$unicum = $treasury[$id];
			$unicum->setDescription($description);
			$this->message(DescribeUnicumMessage::class)->e($unicum);
		} else {
			$this->message(DescribeNoUnicumMessage::class)->p((string)$id);
		}
	}

	private function describeRealm(string $description): void {
		$region = $this->unit->Region();
		$realm  = $region->Realm();
		if ($realm) {
			$possessions = $this->unit->Party()->Possessions();
			if ($possessions->has($realm->Identifier())) {
				$possession = $possessions[$realm->Identifier()];
				if ($possession === $realm) {
					if ($realm->Territory()->Central() === $region) {
						$realm->setDescription($description);
						$this->message(DescribeRealmMessage::class);
					} else {
						$this->message(DescribeRealmCentralMessage::class)->p($realm->Name());
					}
					return;
				}
			}
		}
		$this->message(DescribeRealmNotFoundMessage::class);
	}

	private function setContinentDescription(string $description): void {
		$continent = $this->unit->Region()->Continent();
		if ($continent) {
			$party = $this->unit->Party();
			if (empty($description)) {
				$continent->setDescriptionFor($party);
				$this->message(DescribeContinentUndoMessage::class, $party)->p($continent->Name());
			} else {
				$continent->setDescriptionFor($party, $description);
				$this->message(DescribeContinentMessage::class, $party)->p($continent->Name());
			}
		} else {
			$this->message(DescribeNoContinentMessage::class);
		}
	}
}
