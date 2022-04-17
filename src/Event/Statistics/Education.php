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
use Lemuria\Model\Fantasya\Party\Type;

/**
 * Collect talent statistics for the parties.
 */
final class Education extends AbstractEvent
{
	use StatisticsTrait;

	public function __construct(State $state) {
		parent::__construct($state, Priority::AFTER);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::PARTY) as $party /* @var Party $party */) {
			if ($party->Type() === Type::PLAYER) {
				$this->placeMetrics(Subject::Education, $party);
			}
		}
	}
}
