<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Message\LemuriaMessage;
use Lemuria\Engine\Lemuria\Message\Party\OriginMessage;
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
					$this->setToRegion(Region::get($id));
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

	private function setFromParty(Id $id): void {
		// TODO: Check if we have TELL relation to party.
		$party = Party::get($id);
		$this->setToRegion($party->Origin());
	}

	private function setToRegion(Region $region): void {
		// TODO: Check if region is known.
		$this->unit->Party()->setOrigin($region);
		$this->message(OriginMessage::class)->e($region, OriginMessage::REGION);
	}
}
