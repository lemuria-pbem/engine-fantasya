<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Consumer;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Factory\RealmTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Factory\WorkloadTrait;
use Lemuria\Engine\Fantasya\Message\Unit\AllocationSiegeMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\Realm\Allotment;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Unit;

/**
 * Base class for all commands that request resources from a Region.
 */
abstract class AllocationCommand extends UnitCommand implements Consumer
{
	use RealmTrait;
	use SiegeTrait;
	use WorkloadTrait;

	private const QUOTA = 1.0;

	protected Resources $resources;

	protected ?Allotment $allotment = null;

	protected ?array $lastCheck = null;

	protected bool $isRunCentrally;

	private bool $logCommit = false;

	/**
	 * Create a new command for given Phrase.
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->resources = new Resources();
	}

	public function Unit(): Unit {
		return $this->unit;
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
	 * @return array<Party>
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
		if ($this->isSieged($this->unit->Construction())) {
			$this->message(AllocationSiegeMessage::class);
			return;
		}

		$this->isRunCentrally = $this->isRunCentrally($this);
		$this->initWorkload();
		if ($this->isRunCentrally) {
			$this->allotment = $this->createAllotment($this);
			Lemuria::Log()->debug('New allotment helper for realm ' . $this->allotment->Realm()->Id() . '.', ['command' => $this]);
		}

		$this->createDemand();
		if (count($this->resources)) {
			if (!$this->isRunCentrally) {
				$this->context->getAllocation($this->unit->Region())->register($this);
			}
		} else {
			Lemuria::Log()->debug('Allocation registration skipped due to empty demand.', ['command' => $this]);
		}
	}

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		if (count($this->resources)) {
			if ($this->allotment) {
				$this->allotment->distribute($this);
			} else {
				$this->context->getAllocation($this->unit->Region())->distribute($this);
			}
		}
		$this->logCommit = true;
		$this->commitCommand($this);
	}

	/**
	 * Determine the demand.
	 */
	abstract protected function createDemand(): void;

	protected function commitCommand(UnitCommand $command): void {
		if (!$this->resources->isEmpty()) {
			parent::commitCommand($command);
		} elseif ($this->logCommit) {
			$this->context->getProtocol($this->unit)->logCurrent($command);
		}
	}

	/**
	 * Do the check before allocation.
	 *
	 * @return array<Party>
	 */
	protected function getCheckBeforeAllocation(): array {
		return [];
	}

	/**
	 * Get a resource.
	 */
	protected function getResource(string $class): Quantity {
		if (!isset($this->resources[$class])) {
			$commodity = self::createCommodity($class);
			return new Quantity($commodity, 0);
		}
		return $this->resources[$class];
	}

	protected function hasRegionResources(Commodity $commodity): bool {
		$resources = $this->unit->Region()->Resources();
		return $resources[$commodity]->Count() > 0;
	}
}
