<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Consumer;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Unit;

/**
 * Base class for all commands that request resources from a Region.
 */
abstract class AllocationCommand extends UnitCommand implements Consumer
{
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
	#[Pure] public function getDemand(): Resources {
		return $this->resources;
	}

	/**
	 * Get the requested resource quota that is available for allocation.
	 */
	#[Pure] public function getQuota(): float {
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
		$this->createDemand();
		if (count($this->resources)) {
			$this->context->getAllocation($this->unit->Region())->register($this);
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

		/* @var Quantity $quantity */
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
	 * Check region guards before allocation.
	 *
	 * If region is guarded by other parties and there are no specific agreements, this unit may only produce if it is
	 * not in a building and has better camouflage than all the blocking guards' perception.
	 *
	 * @return Party[]
	 */
	protected function getCheckByAgreement(int $agreement): array {
		$guardParties = [];
		$party        = $this->unit->Party();
		$context      = $this->context;
		$intelligence = $context->getIntelligence($this->unit->Region());
		$camouflage   = PHP_INT_MIN;
		if (!$this->unit->Construction()) {
			$camouflage = $this->calculus()->knowledge(Camouflage::class)->Level();
		}

		foreach ($intelligence->getGuards() as $guard /* @var Unit $guard */) {
			$guardParty = $guard->Party();
			if ($guardParty !== $party) {
				if (!$guardParty->Diplomacy()->has($agreement, $this->unit)) {
					$perception = $context->getCalculus($guard)->knowledge(Perception::class)->Level();
					if ($perception >= $camouflage) {
						$guardParties[$guardParty->Id()->Id()] = $guardParty;
					}
				}
			}
		}

		return $guardParties;
	}

	/**
	 * Determine the demand.
	 */
	abstract protected function createDemand(): void;
}
