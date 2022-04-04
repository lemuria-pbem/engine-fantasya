<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Statistics;

use Lemuria\Identifiable;
use Lemuria\Lemuria;
use Lemuria\Statistics\Metrics\BaseMetrics;

trait StatisticsTrait
{
	protected function placeMetrics(Subject $subject, ?Identifiable $entity = null): void {
		$message = new BaseMetrics($subject->name, $entity);
		Lemuria::Statistics()->enqueue($message);
	}
}
