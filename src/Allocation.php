<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Lemuria;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Lemuria\Exception\AllocationException;
use Lemuria\Engine\Lemuria\Factory\CommandPriority;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Lemuria\Commodity\Camel;
use Lemuria\Model\Lemuria\Commodity\Elephant;
use Lemuria\Model\Lemuria\Commodity\Food;
use Lemuria\Model\Lemuria\Commodity\Horse;
use Lemuria\Model\Lemuria\Commodity\Silver;
use Lemuria\Model\Lemuria\Factory\BuilderTrait;
use Lemuria\Model\Lemuria\Quantity;
use Lemuria\Model\Lemuria\Region;
use Lemuria\Model\Lemuria\Resources;

/**
 * Helper for resource distribution.
 */
final class Allocation
{
	public const POOL_COMMODITIES = [
		Food::class => true, Silver::class => true,
		Camel::class, Elephant::class, Horse::class
	];

	use BuilderTrait;

	private CommandPriority $priority;

	/**
	 * @var array(int=>Consumer)
	 */
	private array $consumers = [];

	/**
	 * @var array(int=>bool)
	 */
	private array $consumersLeft = [];

	/**
	 * @var array(int=>array)
	 */
	private array $rounds = [];

	/**
	 * @var array(int=>Resources)
	 */
	private array $allocations = [];

	/**
	 * @var array(string=>Quantity)
	 */
	private array $resources = [];

	/**
	 * @var array(string=>array)
	 */
	private array $distribution;

	private int $round = 0;

	public function __construct(private Availability $availability) {
		Lemuria::Log()->debug('New allocation helper for region ' . $this->Region()->Id() . '.', ['allocation' => $this]);
		$this->priority = CommandPriority::getInstance();
	}

	/**
	 * Register a Consumer.
	 */
	public function register(Consumer $consumer): Allocation {
		$id                           = $consumer->getId();
		$priority                     = $this->priority->getPriority($consumer);
		$this->rounds[$priority][$id] = $id;
		$this->consumers[$id]         = $consumer;
		$this->consumersLeft[$id]     = true;
		$this->allocations[$id]       = new Resources();
		Lemuria::Log()->debug('Consumer #' . $id . ' registered for region ' . $this->Region()->Id() .' (demand: ' . $this->debugDemand($consumer) . ').', ['consumer' => $consumer]);
		return $this;
	}

	/**
	 * Start resource distribution.
	 */
	public function distribute(Consumer $consumer): void {
		$id = $consumer->getId();
		if (!isset($this->consumers[$id])) {
			throw new AllocationException($consumer, $this->Region());
		}
		if ($consumer->checkBeforeAllocation()) {
			$this->unregister($consumer);
		}
		unset($this->consumersLeft[$id]);

		$round = $this->priority->getPriority($consumer);
		if ($round > $this->round) {
			if (empty($this->consumersLeft)) {
				$this->analyze($round);
				foreach (array_keys($this->distribution) as $class) {
					$this->fillDemand($class);
				}
				$this->createAllocations();
				$this->round = $round;
				foreach ($this->consumers as $consumer) {
					$this->allocate($consumer);
				}
			}
		}
	}

	#[Pure] private function Region(): Region {
		return $this->availability->Region();
	}

	/**
	 * Remove a Consumer.
	 */
	private function unregister(Consumer $consumer): Allocation {
		$id       = $consumer->getId();
		$priority = $this->priority->getPriority($consumer);
		unset($this->rounds[$priority][$id]);
		unset($this->consumers[$id]);
		unset($this->allocations[$id]);
		Lemuria::Log()->debug('Consumer #' . $id . ' unregistered for region ' . $this->Region()->Id() . '.');
		return $this;
	}

	/**
	 * Analyze the demand.
	 */
	private function analyze(int $round): void {
		$this->distribution = [];
		foreach ($this->rounds[$round] as $id) {
			/* @var Consumer $consumer */
			$consumer = $this->consumers[$id];
			$quota    = $consumer->getQuota();
			foreach ($consumer->getDemand() as $class => $quantity /* @var Quantity $quantity */) {
				$demand = $quantity->Count();
				if (!isset($this->distribution[$class])) {
					$this->distribution[$class] = ['total' => 0, 'quota' => $quota, 'demand' => [], 'allocation' => []];
				}
				if ($quota !== $this->distribution[$class]['quota']) {
					throw new LemuriaException('Quota mismatch.');
				}
				$this->distribution[$class]['total']          += $demand;
				$this->distribution[$class]['demand'][$id]     = $demand;
				$this->distribution[$class]['allocation'][$id] = 0;
			}
		}
	}

	/**
	 * Fill every demand.
	 */
	private function fillDemand(string $class): void {
		$reserve = $this->getReserve($class)->Count();
		$quota   = $this->distribution[$class]['quota'];
		$q       = (int)floor($quota * $reserve);
		$qBefore = $q;
		while ($q > 0 && $this->distribution[$class]['total'] > 0) {
			if ($this->distribution[$class]['total'] <= $q) {
				foreach (array_keys($this->distribution[$class]['demand']) as $id) {
					$demand                                        = $this->distribution[$class]['demand'][$id];
					$this->distribution[$class]['allocation'][$id] = $demand;
					unset($this->distribution[$class]['demand'][$id]);
					$this->distribution[$class]['total'] -= $demand;
					$q                                   -= $demand;
				}
			} else {
				$n = count($this->distribution[$class]['demand']);
				$d = (int)($q / $n);
				if ($d > 0) {
					$given                                = $this->giveDemand($class, $d);
					$this->distribution[$class]['total'] -= $given;
					$q                                   -= $given;
				}
				$r = $q % $n;
				if ($r > 0) {
					$this->giveRandom($class, $r);
					$this->distribution[$class]['total'] -= $r;
					$q                                   -= $r;
				}
			}
		}
		$this->reduceReserve($class, $qBefore - $q);
	}

	/**
	 * Give equal allocation to all demands.
	 */
	private function giveDemand(string $class, int $count): int {
		$given = 0;
		foreach ($this->distribution[$class]['demand'] as $id => $demand) {
			$gift                                           = min($count, $demand);
			$this->distribution[$class]['demand'][$id]     -= $gift;
			$this->distribution[$class]['allocation'][$id] += $gift;
			$given                                         += $gift;
			if ($gift === $demand) {
				unset($this->distribution[$class]['demand'][$id]);
			}
		}
		return $given;
	}

	/**
	 * Give one item randomly to a limited number of consumers.
	 */
	private function giveRandom(string $class, int $count): void {
		$consumers = array_keys($this->distribution[$class]['demand']);
		$selected  = $count > 1 ? array_rand($consumers) : [array_rand($consumers)];
		foreach ($selected as $index) {
			$consumer = $consumers[$index];
			$this->distribution[$class]['allocation'][$consumer]++;
			$this->distribution[$class]['demand'][$consumer]--;
			if ($this->distribution[$class]['demand'][$consumer] === 0) {
				unset($this->distribution[$class]['demand'][$consumer]);
			}
		}
	}

	/**
	 * Create allocations after filling the demands.
	 */
	private function createAllocations(): void {
		foreach ($this->distribution as $class => $distribution) {
			$commodity = self::createCommodity($class);
			foreach ($distribution['allocation'] as $id => $count) {
				/* @var Resources $resources */
				$resources = $this->allocations[$id];
				$resources->add(new Quantity($commodity, $count));
			}
		}
	}

	/**
	 * Allocate distributed resources to a specific consumer.
	 */
	private function allocate(Consumer $consumer): void {
		$id = $consumer->getId();
		if (!isset($this->allocations[$id])) {
			throw new AllocationException($consumer, $this->Region());
		}

		/* @var Resources $allocation */
		$allocation = $this->allocations[$id];
		$consumer->allocate($allocation);
		unset($this->allocations[$id]);
		foreach ($allocation as $quantity /* @var Quantity $quantity */) {
			$this->availability->remove($quantity);
		}
	}

	/**
	 * Get current region reserve of given commodity.
	 */
	private function getReserve(string $class): Quantity {
		if (!isset($this->resources[$class])){
			$commodity               = self::createCommodity($class);
			$reserve                 = $this->availability->getResource($commodity)->Count();
			$resource                = new Quantity($commodity, $reserve);
			$this->resources[$class] = $resource;
			Lemuria::Log()->debug('Allocation reserve calculated.', ['resource' => (string)$resource, 'region' => (string)$this->Region()->Id()]);
		}
		return $this->resources[$class];
	}

	/**
	 * Update a region reserve of given commodity.
	 */
	private function reduceReserve(string $class, int $count): void {
		if (!isset($this->resources[$class])) {
			throw new LemuriaException('Reserve of commodity ' . $class . ' not found.');
		}
		/* @var Quantity $quantity */
		$quantity = $this->resources[$class];
		if ($count < 0 || $count > $quantity->Count()) {
			throw new LemuriaException('Invalid reduce count given for reserve update.');
		}

		$quantity->remove(new Quantity($quantity->Commodity(), $count));
	}

	/**
	 * Debug consumer demand.
	 */
	private function debugDemand(Consumer $consumer): string {
		$demand = [];
		foreach ($consumer->getDemand() as $quantity /* @var Quantity $quantity */) {
			$demand[] = (string)$quantity;
		}
		return implode(',', $demand);
	}
}
