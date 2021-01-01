<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Message\Unit\HelpMessage;
use Lemuria\Engine\Lemuria\Message\Unit\HelpNotMessage;
use Lemuria\Engine\Lemuria\Message\Unit\HelpPartyMessage;
use Lemuria\Engine\Lemuria\Message\Unit\HelpPartyNotMessage;
use Lemuria\Engine\Lemuria\Message\Unit\HelpPartyRegionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\HelpPartyRegionNotMessage;
use Lemuria\Engine\Lemuria\Message\Unit\HelpRegionMessage;
use Lemuria\Engine\Lemuria\Message\Unit\HelpRegionNotMessage;
use Lemuria\Engine\Lemuria\Message\Unit\HelpSelfMessage;
use Lemuria\Engine\Lemuria\Message\Unit\HelpUnknownPartyMessage;
use Lemuria\Id;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Lemuria\Party;
use Lemuria\Model\Lemuria\Relation;

/**
 * This command is used to set diplomatic relations.
 *
 * - HELFEN 0|<Party> Abbau|Abbauen|Mache|Machen|Resourcen|Ressourcen [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Alles [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Betrete|Betreten [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Bewache|Bewachen [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Gib|Geben [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Handel|Handeln [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Kampf|Kämpfe|Kämpfen [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Nahrung [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Nicht|Nichts [Region]
 * - HELFEN 0|<Party> Parteitarnung [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Silber [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Tarne|Tarnen|Tarnung [Nicht|Region] [Region|Nicht]
 * - HELFEN 0|<Party> Versorge|Versorgen|Versorgung [Nicht|Region] [Region|Nicht]
 */
final class Help extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 2) {
			throw new InvalidCommandException($this);
		}

		$party = null;
		$p = $this->phrase->getParameter();
		if ($p === '0') {
			$party = $this->unit->Party();
		} else {
			$partyId = Id::fromId($p);
			if ($partyId->Id() === $this->unit->Party()->Id()) {
				$this->message(HelpSelfMessage::class);
				return;
			}
			try {
				$party = Party::get($partyId);
				if (!$this->unit->Party()->Diplomacy()->isKnown($party)) {
					$party = null;
				}
			} catch (NotRegisteredException) {
			}
			if (!$party) {
				$this->message(HelpUnknownPartyMessage::class)->p($partyId->Id());
				return;
			}
		}

		$p         = $this->phrase->getParameter(2);
		$agreement = $this->getAgreement($p);
		if ($agreement === null) {
			throw new InvalidCommandException($this, 'Invalid agreement.');
		}

		$isNot    = false;
		$inRegion = false;
		if ($n > 2) {
			$p = $this->phrase->getParameter(3);
			$this->parseAdditionalParameter($p, $isNot, $inRegion);
		}
		if ($n > 3) {
			$p = $this->phrase->getParameter(4);
			$this->parseAdditionalParameter($p, $isNot, $inRegion);
		}
		if ($n > 4 || $agreement === Relation::NONE && $isNot) {
			throw new InvalidCommandException($this, 'Invalid negation of agreement NONE.');
		}

		$this->updateDiplomacy($party, $inRegion, $agreement, $isNot);
	}

	private function getAgreement(string $agreement): ?int {
		switch (strtolower($agreement)) {
			case 'abbau' :
			case 'abbauen' :
			case 'mache' :
			case 'machen' :
			case 'resourcen' :
			case 'ressourcen' :
				return Relation::RESOURCES;
			case 'alles' :
				return Relation::ALL;
			case 'betrete' :
			case 'betreten' :
				return Relation::ENTER;
			case 'bewache' :
			case 'bewachen' :
				return Relation::GUARD;
			case 'gib' :
			case 'geben' :
				return Relation::GIVE;
			case 'handel' :
			case 'handeln' :
				return Relation::TRADE;
			case 'kampf' :
			case 'kämpfe' :
			case 'kämpfen' :
				return Relation::COMBAT;
			case 'nahrung' :
				return Relation::FOOD;
			case 'nicht' :
			case 'nichts' :
				return Relation::NONE;
			case 'parteitarnung' :
				return Relation::DISGUISE;
			case 'silber' :
				return Relation::SILVER;
			case 'tarne' :
			case 'tarnen' :
			case 'tarnung' :
				return Relation::PERCEPTION;
			case 'versorge' :
			case 'versorgen' :
			case 'versorgung' :
				return Relation::EARN;
			default :
				return null;
		}
	}

	/**
	 * @throws InvalidCommandException
	 */
	private function parseAdditionalParameter(string $p, bool &$isNot, bool &$inRegion): void {
		switch (strtolower($p)) {
			case 'nicht' :
				$isNot = true;
				break;
			case 'region' :
				$inRegion = true;
				break;
			default :
				throw new InvalidCommandException($this);
		}
	}

	private function updateDiplomacy(Party $party, bool $inRegion, int $agreement, bool $isNot): void {
		$diplomacy = $this->unit->Party()->Diplomacy();
		$region    = $inRegion ? $this->unit->Region() : null;
		$relation  = new Relation($party, $region);
		if ($diplomacy->offsetExists($relation)) {
			$relation = $diplomacy->offsetGet($relation);
		} else {
			$diplomacy->add($relation);
		}

		if ($isNot) {
			$relation->remove($agreement);
		} else {
			$relation->add($agreement);
		}

		if ($inRegion) {
			if ($party === $this->unit->Party()) {
				if ($isNot) {
					$this->message(HelpRegionNotMessage::class)->e($region, HelpRegionNotMessage::REGION)->p($agreement);
				} else {
					$this->message(HelpRegionMessage::class)->e($region, HelpRegionMessage::REGION)->p($agreement);
				}
			} else {
				if ($isNot) {
					$this->message(HelpPartyRegionNotMessage::class)->e($party, HelpPartyRegionNotMessage::PARTY)->e($region, HelpPartyRegionMessage::REGION)->p($agreement);
				} else {
					$this->message(HelpPartyRegionMessage::class)->e($party, HelpPartyRegionMessage::PARTY)->e($region, HelpPartyRegionMessage::REGION)->p($agreement);
				}
			}
		} else {
			if ($party === $this->unit->Party()) {
				if ($isNot) {
					$this->message(HelpNotMessage::class)->p($agreement);
				} else {
					$this->message(HelpMessage::class)->p($agreement);
				}
			} else {
				if ($isNot) {
					$this->message(HelpPartyNotMessage::class)->e($party, HelpPartyNotMessage::PARTY)->p($agreement);
				} else {
					$this->message(HelpPartyMessage::class)->e($party, HelpPartyMessage::PARTY)->p($agreement);
				}
			}
		}
	}
}
