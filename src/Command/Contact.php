<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Census;
use Lemuria\Engine\Fantasya\Effect\ContactEffect;
use Lemuria\Engine\Fantasya\Exception\CommandException;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\ContactTrait;
use Lemuria\Engine\Fantasya\Factory\ReassignTrait;
use Lemuria\Engine\Fantasya\Message\Unit\ContactMessage;
use Lemuria\Engine\Fantasya\Message\Unit\ContactNotFoundMessage;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\Model\Reassignment;

/**
 * This command is used to set temporary diplomatic relations that allow a unit to earn silver, produce resources,
 * trade, enter constructions and ships, pass guards and give inventory.
 *
 * - KONTAKTIEREN <Unit> [<Unit> ...]
 */
final class Contact extends UnitCommand implements Reassignment
{
	use ContactTrait;
	use ReassignTrait;

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
				$this->message(ContactNotFoundMessage::class)->p((string)$id);
				return;
			}
			$party = $unit ? $census->getParty($unit) : null;
			if ($unit && $party !== $we && $unit->Region() === $region && $this->checkVisibility($this->unit, $unit)) {
				$diplomacy->contact($unit);
				$diplomacy->knows($party);
				$this->createEffect($party, $unit);
				$this->message(ContactMessage::class)->e($unit);
			} else {
				$this->message(ContactNotFoundMessage::class)->p((string)$id);
			}
		}
	}

	private function createEffect(Party $party, Unit $from): void {
		$effect   = new ContactEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setParty($party));
		if ($existing) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
		}
		$effect->From()->add($from);
	}
}
