<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Lemuria\Event;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Action;
use Lemuria\Engine\Lemuria\Factory\Workplaces;
use Lemuria\Engine\Lemuria\Factory\WorkplacesTrait;
use Lemuria\Engine\Lemuria\Message\Region\PopulationGrowthMessage;
use Lemuria\Engine\Lemuria\State;
use Lemuria\Lemuria;
use Lemuria\Model\Catalog;
use Lemuria\Model\Lemuria\Commodity;
use Lemuria\Model\Lemuria\Commodity\Peasant;
use Lemuria\Model\Lemuria\Commodity\Silver;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Region;

/**
 * Peasants work for their living and increase their silver reserve.
 */
final class Population extends AbstractEvent
{
	use WorkplacesTrait;

	public const RATE = 0.01;

	private Workplaces $workplaces;

	private Commodity $peasant;

	private Commodity $silver;

	#[Pure] public function __construct(State $state) {
		parent::__construct($state, Action::AFTER);
		$this->workplaces = new Workplaces();
		$this->peasant    = self::createCommodity(Peasant::class);
		$this->silver     = self::createCommodity(Silver::class);
	}

	protected function run(): void {
		foreach (Lemuria::Catalog()->getAll(Catalog::LOCATIONS) as $region /* @var Region $region */) {
			$resources = $region->Resources();
			$peasants  = $resources[$this->peasant]->Count();
			if ($peasants <= 0) {
				continue;
			}

			$available = $this->getAvailableWorkplaces($region);
			$reserve   = $resources[$this->silver]->Count();
			$wealth    = $reserve / $peasants / Subsistence::SILVER;
			$years     = $wealth / 24;

			$rate = self::RATE;
			if ($available > 0) {
				$rate += $years * self::RATE;
			} elseif ($years <= 0.0) {
				$rate = 0.0;
			} elseif ($years < 1.0) {
				$rate /= 10;
			}
			$growth = (int)ceil($rate * $peasants);
			if ($growth > 0) {
				$quantity = new Quantity($this->peasant, $growth);
				$resources->add($quantity);
				$this->message(PopulationGrowthMessage::class, $region)->i($quantity);
			}


		}
	}

	private function countNeighbourTrees(Region $region): int {
		$trees = 0;
		foreach (Lemuria::World()->getNeighbours($region)->getAll() as $neighbour /* @var Region $neighbour */) {
			$trees += $neighbour->Resources()[$this->tree]->Count();
		}
		return $trees;
	}
}
