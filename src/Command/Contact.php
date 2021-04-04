<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\ContactMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ContactNotFoundMessage;

/**
 * This command is used to set temporary diplomatic relations that allow a unit to earn silver, produce resources,
 * trade, enter constructions and ships, pass guards and give inventory.
 *
 * - KONTAKTIEREN <Unit> [<Unit> ...]
 */
final class Contact extends UnitCommand
{
	use CamouflageTrait;

	protected function run(): void {
		$n = $this->phrase->count();
		if ($n < 1) {
			throw new InvalidCommandException($this);
		}

		$region    = $this->unit->Region();
		$we        = $this->unit->Party();
		$diplomacy = $we->Diplomacy();
		$i         = 1;
		while ($i <= $n) {
			$id    = null;
			$unit  = $this->nextId($i, $id);
			$party = $unit?->Party();
			if ($unit && $party !== $we && $unit->Region() === $region && $this->checkVisibility($this->calculus(), $unit)) {
				$diplomacy->contact($unit);
				$diplomacy->knows($party);
				$this->message(ContactMessage::class)->e($unit);
			} else {
				$this->message(ContactNotFoundMessage::class)->p($id);
			}
		}
	}
}
