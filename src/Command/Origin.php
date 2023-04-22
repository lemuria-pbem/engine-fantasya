<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Message\LemuriaMessage;
use Lemuria\Engine\Fantasya\Message\Party\OriginMessage;
use Lemuria\Engine\Fantasya\Message\Party\OriginNotVisitedMessage;
use Lemuria\Engine\Fantasya\Message\Party\OriginUntoldMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Entity;
use Lemuria\Id;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Reassignment;

/**
 * This command is used to set the party's origin in the world, allowing to synchronize coordinates between different
 * parties.
 *
 * - URSPRUNG
 * - URSPRUNG Partei <Partei>
 * - URSPRUNG Region <Region>
 */
final class Origin extends UnitCommand implements Reassignment
{
	use ReassignTrait;

	protected function run(): void {
		$n = count($this->phrase);
		if ($n === 0) {
			$this->setToRegion($this->unit->Region());
		} elseif ($n === 2) {
			$id = $this->parseId(2);
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

	protected function checkSize(): bool {
		return true;
	}

	protected function initMessage(LemuriaMessage $message, ?Entity $target = null): LemuriaMessage {
		return $message->setAssignee($this->unit->Party()->Id());
	}

	protected function checkReassignmentDomain(Domain $domain): bool {
		if (count($this->phrase) === 2 && strtolower($this->phrase->getParameter()) === 'partei') {
			return $domain === Domain::Party;
		}
		return false;
	}

	protected function getReassignPhrase(string $old, string $new): ?Phrase {
		return $this->getReassignPhraseForParameter(2, $old, $new);
	}

	/**
	 * A parties' origin can be set if that party has told us about it.
	 */
	private function setFromParty(Id $id): void {
		$party = Party::get($id);
		if ($this->unit->Party()->Diplomacy()->Acquaintances()->isTold($party)) {
			$this->setToRegion($party->Origin());
		} else {
			$this->message(OriginUntoldMessage::class)->e($party);
		}
	}

	/**
	 * A region can be set as origin if we have visited it.
	 */
	private function setFromRegion(Region $region): void {
		if ($this->unit->Party()->Chronicle()->has($region->Id())) {
			$this->setToRegion($region);
		} else {
			$this->message(OriginNotVisitedMessage::class)->e($region);
		}
	}

	private function setToRegion(Region $region): void {
		$this->unit->Party()->setOrigin($region);
		$this->message(OriginMessage::class)->e($region);
	}
}
