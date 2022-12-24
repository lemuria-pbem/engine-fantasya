<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event\Statistics;

use Lemuria\Engine\Fantasya\Event\AbstractEvent;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Party\Census;
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Region;

/**
 * Collect economy statistics for the parties.
 */
final class Economy extends AbstractEvent
{
	use StatisticsTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::Party) as $party /* @var Party $party */) {
			if ($party->Type() === Type::Player && !$party->hasRetired()) {
				$this->placeMetrics(Subject::MaterialPool, $party);

				$census = new Census($party);
				foreach ($census->getAtlas() as $region /* @var Region $region */) {
					$people = $census->getPeople($region);
					$this->placeMetrics(Subject::RegionPool, $people->getFirst());
				}
			}
		}
	}
}
