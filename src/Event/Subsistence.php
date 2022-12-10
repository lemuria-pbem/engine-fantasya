<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Effect\CivilCommotionEffect;
use Lemuria\Engine\Fantasya\Factory\Workplaces;
use Lemuria\Engine\Fantasya\Factory\WorkplacesTrait;
use Lemuria\Engine\Fantasya\Message\Region\SubsistenceMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Peasant;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;

/**
 * Peasants work for their living and increase their silver reserve.
 */
final class Subsistence extends AbstractEvent
{
	use StatisticsTrait;
	use WorkplacesTrait;

	public const SILVER = 10;

	public const WAGE = 11;

	private Workplaces $workplaces;

	private Commodity $peasant;

	private Commodity $silver;

	public function __construct(State $state) {
		parent::__construct($state, Priority::Middle);
		$this->workplaces = new Workplaces();
		$this->peasant    = self::createCommodity(Peasant::class);
		$this->silver     = self::createCommodity(Silver::class);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::Location) as $region /* @var Region $region */) {
			$effect = new CivilCommotionEffect($this->state);
			if (Lemuria::Score()->find($effect->setRegion($region))) {
				$this->placeDataMetrics(Subject::Income, 0, $region);
				return;
			}

			$resources = $region->Resources();
			$peasants  = $resources[$this->peasant]->Count();
			if ($peasants > 0) {
				$wage      = $this->context->getIntelligence($region)->getWage(self::WAGE);
				$available = $this->getAvailableWorkplaces($region);
				$this->placeDataMetrics(Subject::Workplaces, $available, $region);
				$workers   = min($peasants, $available);
				$earnings  = $workers * $wage;
				$working   = new Quantity($this->peasant, $workers);
				$silver    = new Quantity($this->silver, $earnings);
				$resources->add($silver);
				$this->message(SubsistenceMessage::class, $region)->i($working)->i($silver, SubsistenceMessage::SILVER)->p($wage);
				$this->placeDataMetrics(Subject::Income, $earnings, $region);
				$this->placeDataMetrics(Subject::Workers, $workers, $region);
			}
		}
	}
}
