<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Statistics;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Model\Fantasya\Party;

/**
 * Collect ethnological statistics for all parties.
 */
final class Ethnology extends AbstractEvent
{
	use StatisticsTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		foreach (Party::all() as $party) {
			if (!$party->hasRetired()) {
				$this->placeMetrics(Subject::RaceUnits, $party);
				$this->placeMetrics(Subject::RacePeople, $party);
			}
		}
	}
}
