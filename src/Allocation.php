<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use function Lemuria\randKeys;
use Lemuria\Engine\Fantasya\Exception\AllocationException;
use Lemuria\Engine\Fantasya\Factory\CommandPriority;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Griffin;
use Lemuria\Model\Fantasya\Commodity\Griffinegg;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;

/**
 * Helper for resource distribution.
 */
final class Allocation
{
	use BuilderTrait;

	/**
	 * @type array<string, true>
	 */
	public const array POOL_COMMODITIES = [
		Silver::class => true, Griffinegg::class => true,
		Camel::class  => true, Elephant::class   => true, Griffin::class => true, Horse::class => true, Pegasus::class => true
	];

	private readonly CommandPriority $priority;

	/**
	 * @var array<int, Consumer>
	 */
	private array $consumers = [];

	/**
	 * @var array<int, bool>
	 */
	private array $consumersLeft = [];

	/**
	 * @var array<int, array>
	 */
	private array $rounds = [];

	/**
	 * @var array<int, Resources>
	 */
	private array $allocations = [];

	/**
	 * @var array<string, Quantity>
	 */
	private array $resources = [];

	/**
	 * @var array<string, array>
	 */
	private array $distribution;

	private int $round = 0;

	public function __construct(private readonly Availability $availability) {
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
		$round = $this->priority->getPriority($consumer);
		if ($round > $this->round) {
			foreach (array_keys($this->consumersLeft) as $id) {
				if (!isset($this->consumers[$id])) {
					throw new AllocationException($consumer, $this->Region());
				}
				$consumer = $this->consumers[$id];
				if ($consumer->checkBeforeAllocation()) {
					$this->unregister($consumer);
				}
				unset ($this->consumersLeft[$id]);
			}

			$this->analyze($round);
			foreach (array_keys($this->distribution) as $class) {
				$this->fillDemand($class);
			}
			$this->createAllocations();
			$this->round = $round;
			foreach ($this->rounds[$round] as $id) {
				$this->allocate($this->consumers[$id]);
			}
			$consumer->addRegion($this->Region(), 1.0);
		}
	}

	private function Region(): Region {
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
			$consumer = $this->consumers[$id];
			$quota    = $consumer->getQuota();
			foreach ($consumer->getDemand() as $class => $quantity) {
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
		$consumers = $this->distribution[$class]['demand'];
		while ($count > 0 && count($consumers) > 0) {
			foreach (randKeys($consumers, min($count, count($consumers))) as $consumer) {
				$this->distribution[$class]['allocation'][$consumer]++;
				$this->distribution[$class]['demand'][$consumer]--;
				if ($this->distribution[$class]['demand'][$consumer] === 0) {
					unset($this->distribution[$class]['demand'][$consumer]);
				}
				$count--;
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

		$allocation = $this->allocations[$id];
		$consumer->allocate($allocation);
		unset($this->allocations[$id]);
		foreach ($allocation as $quantity) {
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
		foreach ($consumer->getDemand() as $quantity) {
			$demand[] = (string)$quantity;
		}
		return implode(',', $demand);
	}
}
