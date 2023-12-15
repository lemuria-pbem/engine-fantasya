<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Vacate;

use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Effect\FreeSpace;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;

/**
 * This command creates a construction effect for the building that the unit leaves, to make it possible for entering
 * units to calculate free space correctly.
 */
final class Abandon extends UnitCommand
{
	use SiegeTrait;

	protected function run(): void {
		$construction = $this->unit->Construction();
		if ($construction) {
			if ($this->initSiege($construction)->canEnterOrLeave($this->unit)) {
				$effect = $this->getEffect($construction);
				$space  = $this->unit->Size();
				$effect->addSpace($space);
				Lemuria::Log()->debug(
					'Leaving unit ' . $this->unit->Id() . ' will add ' . $space . ' free space to construction ' . $construction->Id() . '.'
				);
			} else {
				Lemuria::Log()->debug(
					'Unit ' . $this->unit->Id() . ' cannot abandon construction ' . $construction->Id() . ' due to siege.'
				);
			}
		}
	}

	protected function commitCommand(UnitCommand $command): void {
	}

	private function getEffect(Construction $construction): FreeSpace {
		$state    = State::getInstance();
		$effect   = new FreeSpace($state);
		$existing = Lemuria::Score()->find($effect->setConstruction($construction));
		if ($existing instanceof FreeSpace) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect);
			$state->injectIntoTurn($effect);
		}
		return $effect;
	}
}
