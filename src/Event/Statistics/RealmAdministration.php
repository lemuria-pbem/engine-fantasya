<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Statistics;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\Realm\Wagoner;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Model\Fantasya\Realm;

/**
 * Collect economy statistics for the parties.
 */
final class RealmAdministration extends AbstractEvent
{
	use StatisticsTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		foreach (Realm::all() as $realm) {
			$party   = $realm->Party();
			$fleet   = $this->state->getRealmFleet($realm);
			$maximum = 0;
			$total   = 0;
			foreach ($realm->Territory()->Central()->Residents() as $unit) {
				if ($unit->IsTransporting() && $unit->Party() === $party) {
					$used = $fleet->getUsedCapacity($unit);
					$this->placeDataMetrics(Subject::TransportUsed, $used, $unit);
					$wagoner  = new Wagoner($unit);
					$maximum += $wagoner->Maximum();
					$total   += $used * $wagoner->Maximum();
				}
			}
			if ($maximum > 0) {
				$this->placeDataMetrics(Subject::TransportUsed, $total / $maximum, $realm);
			}
		}
	}
}
