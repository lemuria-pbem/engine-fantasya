<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Lemuria\Message\Party\OriginMessage;
use Lemuria\Engine\Lemuria\Message\Party\OriginNotVisitedMessage;
use Lemuria\Engine\Lemuria\Message\Party\OriginUntoldMessage;
use Lemuria\Id;
use Lemuria\Model\Lemuria\Region;
use Lemuria\Model\Lemuria\Party;

/**
 * This command is used to set the party's origin in the world, allowing to synchronize coordinates between different
 * parties.
 *
 * - URSPRUNG
 * - URSPRUNG Partei <Partei>
 * - URSPRUNG Region <Region>
 */
final class Origin extends UnitCommand
{
	protected function run(): void {
		$n = count($this->phrase);
		if ($n === 0) {
			$this->setToRegion($this->unit->Region());
		} elseif ($n === 2) {
			$id = Id::fromId($this->phrase->getParameter(2));
			switch (strtolower($this->phrase->getParameter())) {
				case 'partei' :
					$this->setFromParty($id);
					break;
				case 'region' :
					$this->setFromRegion(Region::get($id));
					break;
				default :
					throw new InvalidCommandException($this);
			}
		} else {
			throw new InvalidCommandException($this);
		}
	}

	protected function initMessage(LemuriaMessage $message): LemuriaMessage {
		return $message->e($this->context->Party());
	}

	/**
	 * A parties' origin can be set if that party has told us about it.
	 */
	private function setFromParty(Id $id): void {
		$party = Party::get($id);
		if ($this->unit->Party()->Diplomacy()->Acquaintances()->isTold($party)) {
			$this->setToRegion($party->Origin());
		} else {
			$this->message(OriginUntoldMessage::class)->e($party, OriginUntoldMessage::PARTY);
		}
	}

	/**
	 * A region can be set as origin if we have visited it.
	 */
	private function setFromRegion(Region $region): void {
		if ($this->unit->Party()->Chronicle()->has($region->Id())) {
			$this->setToRegion($region);
		} else {
			$this->message(OriginNotVisitedMessage::class)->e($region, OriginNotVisitedMessage::REGION);
		}
	}

	private function setToRegion(Region $region): void {
		$this->unit->Party()->setOrigin($region);
		$this->message(OriginMessage::class)->e($region, OriginMessage::REGION);
	}
}
