<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\GatherMessage;
use Lemuria\Engine\Fantasya\Message\Unit\GatherNotMessage;

/**
 * This command is used to set the unit's loot behaviour.
 *
 * - SAMMELN [Alles]
 * - SAMMELN Nicht|Nichts
 * - SAMMELN Alle Beute
 */
final class Gather extends UnitCommand
{
	protected function run(): void {
		$n = count($this->phrase);
		if ($n === 0) {
			$isLooting = true;
		} elseif ($n === 1) {
			try {
				$isLooting = match (strtolower($this->phrase->getParameter())) {
					'alles'           => true,
					'nicht', 'nichts' => false
				};
			} catch (\UnhandledMatchError $e) {
				throw new InvalidCommandException($this, previous: $e);
			}
		} elseif ($n === 2) {
			$all  = strtolower($this->phrase->getParameter());
			$loot = strtolower($this->phrase->getParameter(2));
			if ($all !== 'alle' || $loot !== 'beute') {
				throw new InvalidCommandException($this);
			}
			$isLooting = true;
		} else {
			throw new InvalidCommandException($this);
		}

		$this->unit->setIsLooting($isLooting);
		if ($isLooting) {
			$this->message(GatherMessage::class);
		} else {
			$this->message(GatherNotMessage::class);
		}
	}

	protected function checkSize(): bool {
		return true;
	}
}
