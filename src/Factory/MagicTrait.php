<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Calculus;
use Lemuria\Model\Fantasya\Building\Magespire;
use Lemuria\Model\Fantasya\Talent\Magic;
use Lemuria\Model\Fantasya\Unit;

trait MagicTrait
{
	protected function isInActiveMagespire(Unit $unit): bool {
		$construction = $unit->Construction();
		if ($construction?->Building() instanceof Magespire) {
			$size = $construction->Size();
			$sum  = 0;
			foreach ($construction->Inhabitants() as $inhabitant /* @var Unit $inhabitant */) {
				$calculus = new Calculus($inhabitant);
				$sum     += $calculus->knowledge(Magic::class)->Level();
			}
			return $size >= $sum;
		}
		return false;
	}

	protected function reduceGrade(int $grade): int {
		return match(true) {
			$grade <= 1  => $grade,
			$grade <= 5  => $grade - 1,
			$grade <= 10 => $grade - 2,
			$grade <= 25 => $grade - 3,
			$grade <= 50 => $grade - 4,
			default      => $grade - 5,
		};
	}

	protected function reduceConsumption(int $consumption): int {
		return $this->reduceGrade($consumption);
	}
}
