<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command\Create;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Command\UnitCommand;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Job;
use Lemuria\Engine\Fantasya\Factory\WorkloadTrait;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Model\Fantasya\Building\Blacksmith;
use Lemuria\Model\Fantasya\Building\Dockyard;
use Lemuria\Model\Fantasya\Building\Saddlery;
use Lemuria\Model\Fantasya\Building\Workshop;
use Lemuria\Model\Fantasya\Artifact;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Requirement;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Talent\Armory;
use Lemuria\Model\Fantasya\Talent\Bowmaking;
use Lemuria\Model\Fantasya\Talent\Carriagemaking;
use Lemuria\Model\Fantasya\Talent\Shipbuilding;
use Lemuria\Model\Fantasya\Talent\Weaponry;

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
	use CollectTrait;
	use DefaultActivityTrait;
	use WorkloadTrait;

	protected const CONSUMPTION = [
		Armory::class         => [Saddlery::class   => self::CONSUMPTION_RATE],
		Bowmaking::class      => [Blacksmith::class => self::CONSUMPTION_RATE],
		Carriagemaking::class => [Workshop::class   => self::CONSUMPTION_RATE],
		Shipbuilding::class   => [Dockyard::class   => self::CONSUMPTION_RATE],
		Weaponry::class       => [Blacksmith::class => self::CONSUMPTION_RATE]
	];

	private const CONSUMPTION_RATE = 0.5;

	protected int $maximum = 0;

	protected int $capability = 0;

	protected float $consumption = 1.0;

	public function __construct(Phrase $phrase, Context $context, protected Job $job) {
		parent::__construct($phrase, $context);
		$this->initWorkload();
	}

	protected function initialize(): void {
		parent::initialize();
		$this->calculateConsumption();
	}

	protected function calculateConsumption(): void {
		$product = $this->job->getObject();
		if ($product instanceof Artifact) {
			$building = $this->unit->Construction()?->Building();
			if ($building) {
				$talent            = $product->getCraft();
				$this->consumption = self::CONSUMPTION[$talent::class][$building::class] ?? 1.0;
			}
		}
	}

	/**
	 * Get maximum amount that can be produced by knowledge.
	 */
	protected function calculateProduction(Requirement $craft): int {
		$production = 0;
		$talent     = $craft->Talent();
		$cost       = $craft->Level();
		$level      = $this->calculus()->knowledge($talent::class)->Level();
		if ($level >= $cost) {
			$size       = $this->unit->Size();
			$production = (int)floor($this->potionBoost($size) * $size * $level / $cost);
			return $this->reduceByWorkload($production);
		}
		return $production;
	}

	/**
	 * Get maximum amount that can be produced by resources.
	 */
	protected function calculateResources(Resources $resources): int {
		$reserves   = $this->unit->Inventory();
		$production = PHP_INT_MAX;
		foreach ($resources as $quantity /* @var Quantity $quantity */) {
			$commodity    = $quantity->Commodity();
			$resourceNeed = $this->consumption * $quantity->Count();
			$needed       = (int)ceil($this->capability * $resourceNeed);
			$this->collectQuantity($this->unit, $commodity, $needed);
			$reserve    = $reserves->offsetGet($commodity);
			$amount     = (int)floor($reserve->Count() / $resourceNeed);
			$production = min($production, $amount);
			if ($production <= 0) {
				break;
			}
		}
		return $production;
	}
}
