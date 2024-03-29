<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Statistics;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Factory\Workplaces;
use Lemuria\Engine\Fantasya\Factory\WorkplacesTrait;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Model\Fantasya\Navigable;
use Lemuria\Model\Fantasya\Region;

/**
 * Give the census workers something to do.
 */
final class CensusWorkers extends AbstractEvent
{
	use StatisticsTrait;
	use WorkplacesTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->workplaces = new Workplaces();
	}

	protected function run(): void {
		foreach (Region::all() as $region) {
			if ($region->Landscape() instanceof Navigable) {
				continue;
			}

			$available = $this->getAvailableWorkplaces($region);
			$this->placeDataMetrics(Subject::Workplaces, $available, $region);
			$this->placeMetrics(Subject::Infrastructure, $region);
			$this->placeMetrics(Subject::Population, $region);
			$this->placeMetrics(Subject::Unemployment, $region);
			$this->placeMetrics(Subject::Wealth, $region);
		}
	}
}
