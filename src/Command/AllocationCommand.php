<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Consumer;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Factory\WorkloadTrait;
use Lemuria\Engine\Fantasya\Message\Unit\AllocationSiegeMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;

/**
 * Base class for all commands that request resources from a Region.
 */
abstract class AllocationCommand extends UnitCommand implements Consumer
{
	use SiegeTrait;
	use WorkloadTrait;

	private const QUOTA = 1.0;

	protected Resources $resources;

	protected ?array $lastCheck = null;

	/**
	 * Create a new command for given Phrase.
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->resources = new Resources();
	}

	/**
	 * Get the requested resources.
	 */
	public function getDemand(): Resources {
		return $this->resources;
	}

	/**
	 * Get the requested resource quota that is available for allocation.
	 */
	public function getQuota(): float {
		return self::QUOTA;
	}

	/**
	 * Check diplomacy between the unit and region owner and guards.
	 *
	 * This method should return the foreign parties that prevent executing the
	 * command.
	 *
	 * @return Party[]
	 */
	public function checkBeforeAllocation(): array {
		if ($this->lastCheck === null) {
			$this->lastCheck = $this->getCheckBeforeAllocation();
			if (!empty($this->lastCheck)) {
				$this->resources->clear();
			}
		}
		return $this->lastCheck;
	}

	/**
	 * Allocate resources.
	 */
	public function allocate(Resources $resources): void {
		$this->resources = $resources;
	}

	/**
	 * Make preparations before running the command.
	 */
	protected function initialize(): void {
		parent::initialize();
		if (!$this->checkSize() && $this instanceof Activity && $this->IsDefault()) {
			Lemuria::Log()->debug('Allocation command skipped due to empty unit.', ['command' => $this]);
			return;
		}

		$allocation = $this->context->getAllocation($this->unit->Region());
		if ($this->isSieged($this->unit->Construction())) {
			$this->message(AllocationSiegeMessage::class);
			return;
		}

		$this->initWorkload();
		$this->createDemand();
		if (count($this->resources)) {
			$allocation->register($this);
		} else {
			Lemuria::Log()->debug('Allocation registration skipped due to empty demand.', ['command' => $this]);
		}
	}

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		if (count($this->resources)) {
			$this->context->getAllocation($this->unit->Region())->distribute($this);
		}
	}

	/**
	 * Get a resource.
	 */
	protected function getResource(string $class): Quantity {
		if (!isset($this->resources[$class])) {
			$commodity = self::createCommodity($class);
			return new Quantity($commodity, 0);
		}

		/** @var Quantity $quantity */
		$quantity = $this->resources[$class];
		return $quantity;
	}

	/**
	 * Do the check before allocation.
	 *
	 * @return Party[]
	 */
	protected function getCheckBeforeAllocation(): array {
		return [];
	}

	/**
	 * Determine the demand.
	 */
	abstract protected function createDemand(): void;
}
