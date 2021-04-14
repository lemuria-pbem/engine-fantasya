<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Message\Construction\AbstractConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Construction\AnnouncementConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Party\AnnouncementPartyMessage;
use Lemuria\Engine\Fantasya\Message\Region\AnnouncementRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementAnonymousMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementUnitMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\AnnouncementVesselMessage;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Fantasya\Vessel;

/**
 * Send a message to a unit, a party or all units in a region, construction or vessel.
 *
 * - BOTSCHAFT [Einheit] <unit> <text>
 * - BOTSCHAFT Partei <party> <text>
 * - BOTSCHAFT Burg|Gebäude|Gebaeude <construction> <text>
 * - BOTSCHAFT Schiff <vessel> <text>
 * - BOTSCHAFT Region <text>
 */
final class Announcement extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n >= 2) {
			$i = 2;
			switch (strtolower($this->phrase->getParameter())) {
				case 'einheit' :
					$this->sendToUnit($this->nextId($i), $this->getMessage());
					break;
				case 'region' :
					$this->sendToRegion();
					break;
				case 'partei' :
					$id = Id::fromId(strtolower($this->phrase->getParameter($i)));
					$this->sendToParty(Party::get($id));
					break;
				case 'burg' :
				case 'gebäude' :
				case 'gebaeude' :
					$id = Id::fromId(strtolower($this->phrase->getParameter($i)));
					$this->sendToConstruction(Construction::get($id));
					break;
				case 'schiff' :
					$id = Id::fromId(strtolower($this->phrase->getParameter($i)));
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

	private function sendToUnit(Unit $unit, string $message): void {
		$calculus = $this->context->getCalculus($unit);
		if ($calculus->canDiscover($this->unit)) {
			$sender = $this->unit->Name();
			$this->message(AnnouncementUnitMessage::class, $unit)->p($message)->p($sender, AnnouncementUnitMessage::SENDER);
		} else {
			$this->message(AnnouncementAnonymousMessage::class, $unit)->p($message);
		}
	}

	private function sendToParty(Party $party): void {
		$message = $this->getMessage();
		$sender  = $this->unit->Party()->Name();
		$this->message(AnnouncementPartyMessage::class, $party)->p($message)->p($sender, AnnouncementConstructionMessage::SENDER);
	}

	private function sendToConstruction(Construction $construction): void {
		$message = $this->getMessage();
		$sender  = $this->unit->Party()->Name();
		$this->message(AbstractConstructionMessage::class, $construction)->p($message)->p($sender, AnnouncementConstructionMessage::SENDER);
	}

	private function sendToVessel(Vessel $vessel): void {
		$message = $this->getMessage();
		$sender  = $this->unit->Party()->Name();
		$this->message(AnnouncementVesselMessage::class, $vessel)->p($message)->p($sender, AnnouncementVesselMessage::SENDER);
	}

	private function sendToRegion(): void {
		$region  = $this->unit->Region();
		$message = $this->getMessage(2);
		$sender  = $this->unit->Party()->Name();
		$this->message(AnnouncementRegionMessage::class, $region)->p($message)->p($sender, AnnouncementRegionMessage::SENDER);
	}

	private function getMessage(int $i = 3): string {
		$message = $this->phrase->getLine($i);
		return trim($message, "\"'\t ");
	}
}
