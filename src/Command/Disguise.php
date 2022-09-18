<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\DisguiseDoesNotKnowMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguiseKnownPartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguiseMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguiseNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguisePartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguisePartyNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguiseUnknownPartyMessage;
use Lemuria\Id;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Party;

/**
 * This command lets a unit set its camouflage level and allows it to disguise as unit from foreign party or hide its
 * party completely.
 *
 * - TARNEN
 * - TARNEN Nein|Nicht
 * - TARNEN Partei [0|<Party>]
 * - TARNEN Partei Nein|Nicht
 */
final class Disguise extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n <= 0) {
			$this->unit->setIsHiding(true);
			$this->message(DisguiseMessage::class);
			return;
		}
		$parameter = $this->phrase->getParameter();
		$p = strtolower($parameter);
		if ($p === 'partei') {
			if ($n === 1) {
				$this->unit->setDisguise();
				$this->message(DisguisePartyMessage::class);
				return;
			}
			if ($n === 2) {
				$parameter = strtolower($this->phrase->getParameter(2));
				if ($parameter === '0') {
					$this->unit->setDisguise();
					$this->message(DisguisePartyMessage::class);
				} elseif (in_array($parameter, ['nein', 'nicht'])) {
					$this->unit->setDisguise(false);
					$this->message(DisguisePartyNotMessage::class);
				} else {
					$partyId = Id::fromId($parameter);
					try {
						$party = Party::get($partyId);
						if (!$this->unit->Party()->Diplomacy()->isKnown($party)) {
							$this->message(DisguiseDoesNotKnowMessage::class)->e($party);
							$party = null;
						}
					} catch (NotRegisteredException) {
						$party = null;
					}
					if ($party) {
						if ($party === $this->unit->Party()) {
							$this->unit->setDisguise();
							$this->message(DisguisePartyMessage::class);
						} else {
							$this->unit->setDisguise($party);
							$this->message(DisguiseKnownPartyMessage::class)->e($party);
						}
					} else {
						$this->message(DisguiseUnknownPartyMessage::class)->p((string)$partyId);
					}
				}
			}
		} elseif ($n === 1 && in_array($p, ['nein', 'nicht'])) {
			$this->unit->setIsHiding(false);
			$this->message(DisguiseNotMessage::class);
		} else {
			throw new InvalidCommandException($this);
		}
	}

	protected function checkSize(): bool {
		return true;
	}
}
