<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

trait WorkloadTrait
{
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
}
