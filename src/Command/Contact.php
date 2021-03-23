<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\ContactMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ContactNotFoundMessage;
use Lemuria\Id;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Unit;

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
		$i         = 1;
		while ($i <= $n) {
			$id   = null;
			$unit = $this->nextId($i, $id);
			if ($unit && $unit->Region() === $region) {
				$diplomacy->contact($unit);
				$this->message(ContactMessage::class)->e($unit);
			} else {
				$this->message(ContactNotFoundMessage::class)->p($id);
			}
		}
	}
}
