<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\DisguiseDoesNotKnowMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguiseKnownPartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguiseLevelMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguiseMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguiseNotMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguisePartyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\DisguiseUnknownPartyMessage;
use Lemuria\Id;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Party;

/**
 * This command lets a unit set its camouflage level and allows it to disguise as unit from foreign party or hide its
 * party completely.
 *
 * - TARNEN [<Level>]
 * - TARNEN Nein|Nicht
 * - TARNEN Partei [<Party>]
 */
final class Disguise extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n <= 0) {
			$this->unit->setCamouflage(null);
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
				$partyId = Id::fromId($this->phrase->getParameter(2));
				try {
					$party = Party::get($partyId);
					if (!$this->unit->Party()->Diplomacy()->isKnown($party)) {
						$party = null;
						$this->message(DisguiseDoesNotKnowMessage::class)->e($party);
					}
				} catch (NotRegisteredException) {
					$party = null;
				}
				if ($party) {
					$this->unit->setDisguise($party);
					$this->message(DisguiseKnownPartyMessage::class)->e($party);
				} else {
					$this->message(DisguiseUnknownPartyMessage::class)->e($party);
				}
				return;
			}
		} else {
			if ($n === 1) {
				if (in_array($p, ['nein', 'nicht'])) {
					$this->unit->setCamouflage(null);
					$this->message(DisguiseNotMessage::class);
				} else {
					$level = (int)$parameter;
					if ((string)$level === $parameter) {
						$this->unit->setCamouflage($level);
						$this->message(DisguiseLevelMessage::class)->p($level);
						return;
					}
				}
			}
		}
		throw new InvalidCommandException($this);
	}
}
