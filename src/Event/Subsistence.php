<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Event;

use Lemuria\Engine\Fantasya\Effect\CivilCommotionEffect;
use Lemuria\Engine\Fantasya\Factory\Workplaces;
use Lemuria\Engine\Fantasya\Factory\WorkplacesTrait;
use Lemuria\Engine\Fantasya\Message\Region\SubsistenceMessage;
use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
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
	use WorkplacesTrait;

	public const SILVER = 10;

	public const WAGE = 11;

	private Workplaces $workplaces;

	private Commodity $peasant;

	private Commodity $silver;

	public function __construct(State $state) {
		parent::__construct($state, Priority::MIDDLE);
		$this->workplaces = new Workplaces();
		$this->peasant    = self::createCommodity(Peasant::class);
		$this->silver     = self::createCommodity(Silver::class);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Domain::LOCATION) as $region /* @var Region $region */) {
			$effect = new CivilCommotionEffect($this->state);
			if (Lemuria::Score()->find($effect)) {
				return;
			}

			$resources = $region->Resources();
			$peasants  = $resources[$this->peasant]->Count();
			if ($peasants > 0) {
				$wage      = $this->context->getIntelligence($region)->getWage(self::WAGE);
				$available = $this->getAvailableWorkplaces($region);
				$workers   = min($peasants, $available);
				$earnings  = $workers * $wage;
				$working   = new Quantity($this->peasant, $workers);
				$silver    = new Quantity($this->silver, $earnings);
				$resources->add($silver);
				$this->message(SubsistenceMessage::class, $region)->i($working)->i($silver, SubsistenceMessage::SILVER)->p($wage);
			}
		}
	}
}
