<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command;

use Lemuria\Engine\Lemuria\Exception\InvalidCommandException;
use Lemuria\Engine\Lemuria\Message\Unit\ContactMessage;
use Lemuria\Engine\Lemuria\Message\Unit\ContactNotFoundMessage;
use Lemuria\Id;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Lemuria\Unit;

/**
 * This command is used to set temporary diplomatic relations that allow a unit to earn silver, produce resources,
 * trade, enter constructions and ships, pass guards and give inventory.
 *
 * - KONTAKTIEREN <Unit> [<Unit> ...]
 */
final class Contact extends UnitCommand
{
	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 1) {
			throw new InvalidCommandException($this);
		}

		$region    = $this->unit->Region();
		$diplomacy = $this->unit->Party()->Diplomacy();
		for ($i = 1; $i <= $n; $i++) {
			$id = Id::fromId($this->phrase->getParameter($i));
			$unit = null;
			try {
				$unit = Unit::get($id);
			} catch (NotRegisteredException) {
			}
			if ($unit && $unit->Region() === $region) {
				$diplomacy->contact($unit);
				$this->message(ContactMessage::class)->e($unit, ContactMessage::UNIT);
			} else {
				$this->message(ContactNotFoundMessage::class)->p($id->Id());
			}
		}
	}
}
