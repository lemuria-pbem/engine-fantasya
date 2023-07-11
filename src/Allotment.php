<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Exception\AllocationException;
use Lemuria\Engine\Fantasya\Factory\CommandPriority;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity\Camel;
use Lemuria\Model\Fantasya\Commodity\Elephant;
use Lemuria\Model\Fantasya\Commodity\Horse;
use Lemuria\Model\Fantasya\Commodity\Pegasus;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Realm;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Resources;

/**
 * Helper for central resource distribution in realms.
 */
final class Allotment
{
	use BuilderTrait;

	public const POOL_COMMODITIES = [
		Silver::class => true, Camel::class  => true, Elephant::class => true, Horse::class => true, Pegasus::class => true
	];

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

	public function __construct(private readonly Realm $realm) {
	}

	public function Realm(): Realm {
		return $this->realm;
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
		}
	}
}
