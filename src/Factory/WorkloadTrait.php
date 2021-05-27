<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Model\Fantasya\Commodity\Potion\DrinkOfCreation;

trait WorkloadTrait
{
	use ContextTrait;

	protected int $fullProduction;

	protected Workload $workload;

	protected function initWorkload(int $maximum = 1000000): void {
		$this->workload = $this->context->getWorkload($this->unit)->setMaximum($maximum);
	}

	/**
	 * Calculate reduced production for current workload.
	 */
	protected function reduceByWorkload(int $fullProduction): int {
		$this->fullProduction = $fullProduction;
		$remaining            = max(0.0, 1.0 - $this->workload->Percent());
		return (int)round($remaining * $fullProduction);
	}

	protected function addToWorkload(int $production): void {
		$this->workload->add((int)round($production / $this->fullProduction * $this->workload->Maximum()));
	}

	protected function potionBoost(int $unitSize): float {
		$effect     = $this->context->getCalculus($this->unit)->hasApplied(DrinkOfCreation::class);
		$potionSize = $effect?->Count() * DrinkOfCreation::PERSONS;
		return min(2.0, 1.0 + $potionSize / $unitSize);
	}
}
