<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Statistics;

use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Statistics\Data\Number;
use Lemuria\Statistics\Metrics\BaseMetrics;
use Lemuria\Statistics\Metrics\DataMetrics;

trait StatisticsTrait
{
	protected function placeMetrics(Subject $subject, ?Identifiable $entity = null): void {
		if ($this->isNoSimulation()) {
			$message = new BaseMetrics($subject->name, $entity);
			Lemuria::Statistics()->enqueue($message);
		}
	}

	protected function placeDataMetrics(Subject $subject, int|float $data, ?Identifiable $entity = null): void {
		if ($this->isNoSimulation()) {
			$message = new DataMetrics($subject->name, $entity);
			Lemuria::Statistics()->enqueue($message->setData(new Number($data)));
		}
	}

	private function isNoSimulation(): bool {
		return !$this->context->getTurnOptions()->IsSimulation();
	}
}
