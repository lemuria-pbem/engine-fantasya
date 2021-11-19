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

	private function getAdditionalSpace(Construction $construction): int {
		$effect = new FreeSpace(State::getInstance());
		$effect->setConstruction($construction);
		$effect = Lemuria::Score()->find($effect);
		return $effect instanceof FreeSpace ? $effect->Space() : 0;
	}
}
