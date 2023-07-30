<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Message\Announcement as Announce;
use Lemuria\Engine\Fantasya\Message\Construction\AnnouncementConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Party\AnnouncementPartyMessage;
use Lemuria\Engine\Fantasya\Message\Region\AnnouncementRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementAnonymousMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementNoConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementNoPartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementNoUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementNoVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementOwnMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementToConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementToPartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementToRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementToUnitAnonymousMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementToUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementToVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementUnitMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\AnnouncementVesselMessage;
use Lemuria\Engine\Fantasya\Outlook;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;
use Lemuria\Model\Reassignment;

/**
 * Send a message to a unit, a party or all units in a region, construction or vessel.
 *
 * - BOTSCHAFT [Einheit] <unit> <text>
 * - BOTSCHAFT Partei <party> <text>
 * - BOTSCHAFT Burg|Gebäude|Gebaeude <construction> <text>
 * - BOTSCHAFT Schiff <vessel> <text>
 * - BOTSCHAFT Region <text>
 */
final class Announcement extends UnitCommand implements Reassignment
{
	use ReassignTrait;

	private int $reassignParameter;

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n >= 2) {
			$i = 2;
			switch (mb_strtolower($this->phrase->getParameter())) {
				case 'einheit' :
					$this->sendToUnit($this->nextId($i), $this->getMessage());
					break;
				case 'region' :
					$this->sendToRegion();
					break;
				case 'partei' :
					$id = $this->parseId($i);
					$this->sendToParty(Party::get($id));
					break;
				case 'burg' :
				case 'gebäude' :
				case 'gebaeude' :
					$id = $this->parseId($i);
					$this->sendToConstruction(Construction::get($id));
					break;
				case 'schiff' :
					$id = $this->parseId($i);
					$this->sendToVessel(Vessel::get($id));
					break;
				default :
					$i = 1;
					$this->sendToUnit($this->nextId($i), $this->getMessage(2));
			}
		} else {
			throw new UnknownCommandException($this);
		}
	}

	protected function checkReassignmentDomain(Domain $domain): bool {
		$n = $this->phrase->count();
		if ($n >= 2) {
			$this->reassignParameter = 2;
			switch (mb_strtolower($this->phrase->getParameter())) {
				case 'einheit' :
					return $domain === Domain::Unit;
				case 'partei' :
					return $domain === Domain::Party;
				case 'burg' :
				case 'gebäude' :
				case 'gebaeude' :
					return $domain === Domain::Construction;
				case 'schiff' :
					return $domain === Domain::Vessel;
				default :
					$this->reassignParameter = 1;
					return $domain === Domain::Unit;
			}
		}
		return false;
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		return $this->getReassignPhraseForParameter($this->reassignParameter, $old, $new);
	}

	private function sendToUnit(Unit $unit, string $message): void {
		if ($unit === $this->unit) {
			$this->message(AnnouncementOwnMessage::class);
		} elseif ($unit->Region() === $this->unit->Region() && $this->calculus()->canDiscover($unit)) {
			$calculus  = $this->context->getCalculus($unit);
			$recipient = (string)$unit;
			if ($calculus->canDiscover($this->unit)) {
				$sender = (string)$this->unit;
				$this->message(AnnouncementUnitMessage::class, $unit)->p($message)->p($sender, Announce::SENDER)->p($recipient, Announce::RECIPIENT);
				$this->message(AnnouncementToUnitMessage::class)->p($message)->e($unit);
			} else {
				$this->message(AnnouncementAnonymousMessage::class, $unit)->p($message)->p('', Announce::SENDER)->p($recipient, Announce::RECIPIENT);
				$this->message(AnnouncementToUnitAnonymousMessage::class)->p($message)->e($unit);
			}
		} else {
			$this->message(AnnouncementNoUnitMessage::class)->e($unit);
		}
	}

	private function sendToParty(Party $party): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			foreach ($construction->Inhabitants() as $unit) {
				if ($this->trySendToPartyUnit($party, $unit)) {
					return;
				}
			}
		}
		$vessel = $this->unit->Vessel();
		if ($vessel) {
			foreach ($vessel->Passengers() as $unit) {
				if ($this->trySendToPartyUnit($party, $unit)) {
					return;
				}
			}
		}
		$outlook = new Outlook(new Census($this->unit->Party()));
		foreach ($outlook->getApparitions($this->unit->Region()) as $unit) {
			if ($this->trySendToPartyUnit($party, $unit)) {
				return;
			}
		}
		$this->message(AnnouncementNoPartyMessage::class)->e($party);
	}

	private function trySendToPartyUnit(Party $party, Unit $unit): bool {
		if ($unit->Disguise() === $party || $unit->Party() === $party) {
			$message   = $this->getMessage();
			$sender    = (string)$this->unit->Party();
			$recipient = (string)$party;
			$this->message(AnnouncementPartyMessage::class, $party)->p($message)->p($sender, Announce::SENDER)->p($recipient, Announce::RECIPIENT);
			$this->message(AnnouncementToPartyMessage::class)->p($message)->e($party);
			return true;
		}
		return false;
	}

	private function sendToConstruction(Construction $construction): void {
		if ($construction->Region() === $this->unit->Region()) {
			$message   = $this->getMessage();
			$sender    = (string)$this->unit->Party();
			$recipient = (string)$construction;
			$this->message(AnnouncementConstructionMessage::class, $construction)->p($message)->p($sender, Announce::SENDER)->p($recipient, Announce::RECIPIENT);
			$this->message(AnnouncementToConstructionMessage::class)->p($message)->e($construction);
		} else {
			$this->message(AnnouncementNoConstructionMessage::class)->e($construction);
		}
	}

	private function sendToVessel(Vessel $vessel): void {
		if ($vessel->Region() === $this->unit->Region()) {
			$message   = $this->getMessage();
			$sender    = (string)$this->unit->Party();
			$recipient = (string)$vessel;
			$this->message(AnnouncementVesselMessage::class, $vessel)->p($message)->p($sender, Announce::SENDER)->p($recipient, Announce::RECIPIENT);
			$this->message(AnnouncementToVesselMessage::class)->p($message)->e($vessel);
		} else {
			$this->message(AnnouncementNoVesselMessage::class)->e($vessel);
		}
	}

	private function sendToRegion(): void {
		$region    = $this->unit->Region();
		$message   = $this->getMessage(2);
		$sender    = (string)$this->unit->Party();
		$recipient = (string)$region;
		$this->message(AnnouncementRegionMessage::class, $region)->p($message)->p($sender, Announce::SENDER)->p($recipient, Announce::RECIPIENT);
		$this->message(AnnouncementToRegionMessage::class)->p($message)->e($region);
	}

	private function getMessage(int $i = 3): string {
		$message = $this->phrase->getLine($i);
		return trim($message, "\"'\t ");
	}
}
