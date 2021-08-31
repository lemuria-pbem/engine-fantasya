<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Effect\ContactEffect;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\CamouflageTrait;
use Lemuria\Engine\Fantasya\Message\Unit\ContactMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ContactNotFoundMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Unit;

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
		$census    = new Census($we);
		$diplomacy = $we->Diplomacy();
		$i         = 1;
		while ($i <= $n) {
			$id = null;
			try {
				$unit = $this->nextId($i, $id);
			} catch (CommandException) {
				$this->message(ContactNotFoundMessage::class)->p($id);
				return;
			}
			$party = $unit ? $census->getParty($unit) : null;
			if ($unit && $party !== $we && $unit->Region() === $region && $this->checkVisibility($this->calculus(), $unit)) {
				$diplomacy->contact($unit);
				$diplomacy->knows($party);
				$this->createEffect($party, $unit);
				$this->message(ContactMessage::class)->e($unit);
			} else {
				$this->message(ContactNotFoundMessage::class)->p($id);
			}
		}
	}

	protected function checkSize(): bool {
		return true;
	}

	private function createEffect(Party $party, Unit $from): void {
		$effect = new ContactEffect(State::getInstance());
		$effect->setParty($party);
		$existing = Lemuria::Score()->find($effect);
		if ($existing) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
		}
		$effect->From()->add($from);
	}
}
