<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Effect\FreeSpace;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Unit;

trait FreeSpaceTrait
{
	protected function getFreeSpace(Construction $construction): int {
		$space      = $construction->getFreeSpace();
		$additional = $this->getAdditionalSpace($construction);
		return $space + $additional;
	}

	protected function isTooSmall(Construction $construction, Unit $unit): bool {
		return $this->getFreeSpace($construction) < $unit->Size();
	}

	protected function useAdditionalSpace(Construction $construction, Unit $unit): void {
		$size   = $unit->Size();
		$space  = $construction->getFreeSpace();
		$needed = $size - $space;
		if ($needed > 0) {
			$this->getFreeSpaceEffect($construction)->removeSpace($needed);
		}
	}

	private function getAdditionalSpace(Construction $construction): int {
		$effect = $this->getFreeSpaceEffect($construction);
		return $effect ? $effect->Space() : 0;
	}

	private function getFreeSpaceEffect(Construction $construction): ?FreeSpace {
		$effect = new FreeSpace(State::getInstance());
		$effect->setConstruction($construction);
		$effect = Lemuria::Score()->find($effect);
		return $effect instanceof FreeSpace ? $effect : null;
	}
}
