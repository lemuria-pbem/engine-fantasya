<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Vacate;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Effect\FreeSpace;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;

/**
 * This command creates a construction effect for the building that the unit leaves, to make it possible for entering
 * units to calculate free space correctly.
 *
 * - VERLASSEN
 */
final class Abandon extends UnitCommand
{
	protected function run(): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			$effect = $this->getEffect($construction);
			$space  = $this->unit->Size();
			$effect->addSpace($space);
			Lemuria::Log()->debug(
				'Leaving unit ' . $this->unit->Id() . ' will add ' . $space . ' free space to construction ' . $construction->Id() . '.'
			);
		}
	}

	private function getEffect(Construction $construction): FreeSpace {
		$state  = State::getInstance();
		$effect = new FreeSpace($state);
		$effect->setConstruction($construction);
		$existing = Lemuria::Score()->find($effect);
		if ($existing instanceof FreeSpace) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
			$state->injectIntoTurn($effect);
		}
		return $effect;
	}
}
