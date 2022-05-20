<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Message\Construction\AbstractConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Construction\AnnouncementConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Party\AnnouncementPartyMessage;
use Lemuria\Engine\Fantasya\Message\Region\AnnouncementRegionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementAnonymousMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementNoConstructionMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementNoPartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementNoUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementNoVesselMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementToPartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementToUnitAnonymousMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementToUnitMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AnnouncementUnitMessage;
use Lemuria\Engine\Fantasya\Message\Vessel\AnnouncementVesselMessage;
use Lemuria\Engine\Fantasya\Outlook;
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
			switch (mb_strtolower($this->phrase->getParameter())) {
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
		if ($unit->Region() === $this->unit->Region() && $this->calculus()->canDiscover($unit)) {
			$calculus = $this->context->getCalculus($unit);
			if ($calculus->canDiscover($this->unit)) {
				$sender = $this->unit->Name();
				$this->message(AnnouncementUnitMessage::class, $unit)->p($message)->p($sender, AnnouncementUnitMessage::SENDER);
				$this->message(AnnouncementToUnitMessage::class)->p($message)->e($unit);
			} else {
				$this->message(AnnouncementAnonymousMessage::class, $unit)->p($message);
				$this->message(AnnouncementToUnitAnonymousMessage::class)->p($message)->e($unit);
			}
		} else {
			$this->message(AnnouncementNoUnitMessage::class)->e($unit);
		}
	}

	private function sendToParty(Party $party): void {
		$outlook = new Outlook(new Census($this->unit->Party()));
		foreach ($outlook->getApparitions($this->unit->Region()) as $unit /* @var Unit $unit */) {
			if ($unit->Party() === $party) {
				$message = $this->getMessage();
				$sender  = $this->unit->Party()->Name();
				$this->message(AnnouncementPartyMessage::class, $party)->p($message)->p($sender, AnnouncementConstructionMessage::SENDER);
				$this->message(AnnouncementToPartyMessage::class)->p($message)->e($party);
				return;
			}
		}
		$this->message(AnnouncementNoPartyMessage::class)->e($party);
	}

	private function sendToConstruction(Construction $construction): void {
		if ($construction->Region() === $this->unit->Region()) {
			$message = $this->getMessage();
			$sender  = $this->unit->Party()->Name();
			$this->message(AbstractConstructionMessage::class, $construction)->p($message)->p($sender, AnnouncementConstructionMessage::SENDER);
		} else {
			$this->message(AnnouncementNoConstructionMessage::class)->e($construction);
		}
	}

	private function sendToVessel(Vessel $vessel): void {
		if ($vessel->Region() === $this->unit->Region()) {
			$message = $this->getMessage();
			$sender  = $this->unit->Party()->Name();
			$this->message(AnnouncementVesselMessage::class, $vessel)->p($message)->p($sender, AnnouncementVesselMessage::SENDER);
		} else {
			$this->message(AnnouncementNoVesselMessage::class)->e($vessel);
		}
	}

	private function sendToRegion(): void {
		$region  = $this->unit->Region();
		$message = $this->getMessage(2);
		$sender  = $this->unit->Party()->Name();
		$this->message(AnnouncementRegionMessage::class, $region)->p($message)->p($sender, AnnouncementRegionMessage::SENDER);
	}

	#[Pure] private function getMessage(int $i = 3): string {
		$message = $this->phrase->getLine($i);
		return trim($message, "\"'\t ");
	}
}
