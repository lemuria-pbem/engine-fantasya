<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria\Command\Create;

use Lemuria\Engine\Lemuria\Activity;
use Lemuria\Engine\Lemuria\Command\UnitCommand;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Requirement;
use Lemuria\Model\Lemuria\Resources;

/**
 * Implementation of command MACHEN <amount> <product> (create product).
 *
 * The command creates new products from inventory and adds them to the executing unit's inventory.
 *
 * - MACHEN <product>
 * - MACHEN <amount> <product>
 */
abstract class AbstractProduct extends UnitCommand implements Activity
{
	protected string $resource;

	protected ?int $demand = null;

	protected int $capability = 0;

	/**
	 * Make preparations before running the command.
	 */
	protected function initialize(): void {
		parent::initialize();
		$this->resource = $this->phrase->getParameter(0);
		if (count($this->phrase) === 2) {
			$this->demand = (int)$this->phrase->getParameter(1);
		}
	}

	/**
	 * Get maximum amount that can be produced by knowledge.
	 *
	 * @param Requirement $craft
	 * @return int
	 */
	protected function calculateProduction(Requirement $craft): int {
		$production = 0;
		$talent     = $craft->Talent();
		$cost       = $craft->Level();
		$level      = $this->calculus()->knowledge(get_class($talent))->Level();
		if ($level >= $cost) {
			$production = (int)floor($this->unit->Size() * $level / $cost);
		}
		return $production;
	}

	/**
	 * Get maximum amount that can be produced by resources.
	 *
	 * @param Resources $resources
	 * @return int
	 */
	protected function calculateResources(Resources $resources): int {
		$reserves   = $this->unit->Inventory();
		$production = PHP_INT_MAX;
		foreach ($resources as $quantity /* @var Quantity $quantity */) {
			$commodity = $quantity->Commodity();
			$needed    = $this->capability * $quantity->Count();
			$reserve   = $reserves->offsetGet($commodity)->Count();
			if ($reserve < $needed) {
				$resourcePool = $this->context->getResourcePool($this->unit);
				$resourcePool->take($this->unit, new Quantity($commodity, $needed - $reserve));
			}
			$reserve    = $reserves->offsetGet($commodity);
			$amount     = (int)floor($reserve->Count() / $quantity->Count());
			$production = min($production, $amount);
			if ($production <= 0) {
				break;
			}
		}
		return $production;
	}
}
