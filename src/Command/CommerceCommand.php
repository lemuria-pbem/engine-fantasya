<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\DefaultActivityTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Factory\Workload;
use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Site;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Talent\Trading;

/**
 * Base class for all commands that trade in a Region.
 */
abstract class CommerceCommand extends UnitCommand implements Activity, Merchant
{
	use DefaultActivityTrait;
	use SiegeTrait;

	private const ACTIVITY = 'Trade';

	protected Resources $goods;

	protected int $demand;

	protected ?int $maximum = null;

	protected int $remaining;

	protected int $amount;

	protected int $count = 0;

	protected int $cost = 0;

	protected ?array $lastCheck = null;

	protected Commodity $silver;

	protected Workload $trades;

	protected Resources $traded;

	private ?bool $isTradePossible = null;

	private Commodity $commodity;

	/**
	 * Create a new command for given Phrase.
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->goods  = new Resources();
		$this->traded = new Resources();
		$this->silver = self::createCommodity(Silver::class);
	}

	/**
	 * Get the activity class.
	 */
	#[Pure] public function Activity(): string {
		return self::ACTIVITY;
	}

	/**
	 * Get the resources this merchant wants to trade.
	 */
	#[Pure] public function getGoods(): Resources {
		return $this->goods;
	}

	/**
	 * Check diplomacy between the unit and region owner and guards.
	 *
	 * This method should return the foreign parties that prevent executing the
	 * command.
	 *
	 * @return Party[]
	 */
	public function checkBeforeCommerce(): array {
		if ($this->lastCheck === null) {
			$this->lastCheck = $this->getCheckBeforeCommerce();
			if (!empty($this->lastCheck)) {
				$this->goods->clear();
			}
		}
		return $this->lastCheck;
	}

	/**
	 * Make preparations before running the command.
	 */
	protected function initialize(): void {
		parent::initialize();
		if ($this->isTradePossible()) {
			$commerce     = $this->context->getCommerce($this->unit->Region());
			$this->trades = $commerce->getWorkload($this->unit);
			$this->createGoods();
			if (count($this->goods)) {
				$commerce->register($this);
			} else {
				Lemuria::Log()->debug('Commerce registration skipped due to empty demand.', ['command' => $this]);
			}
		} else {
			Lemuria::Log()->debug('Commerce disabled in this region - no castle here.');
		}
	}

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		if (count($this->goods)) {
			$this->context->getCommerce($this->unit->Region())->distribute($this);
		}
	}

	/**
	 * Do the check before allocation.
	 *
	 * @return Party[]
	 */
	protected function getCheckBeforeCommerce(): array {
		return $this->getCheckByAgreement(Relation::TRADE);
	}

	/**
	 * Determine the goods.
	 */
	protected function createGoods(): void {
		$demand       = $this->getDemand();
		$this->amount = min($demand->Count(), $this->getMaximum());
		if ($this->amount > 0) {
			$this->goods->add($demand);
		} else {
			Lemuria::Log()->debug('Merchant ' . $this . ' has no demand.');
		}
	}

	protected function getDemand(): Quantity {
		$n = $this->phrase->count();
		if ($n === 1) {
			$demand       = $this->getMaximum();
			$this->demand = PHP_INT_MAX;
			$luxury       = $this->phrase->getParameter();
		} elseif ($n === 2) {
			$demand       = (int)$this->phrase->getParameter();
			$this->demand = $demand;
			$luxury       = $this->phrase->getParameter(2);
		} else {
			throw new UnknownCommandException($this);
		}
		$this->commodity = $this->context->Factory()->commodity($luxury);
		return new Quantity($this->commodity, $demand);
	}

	protected function getMaximum(): int {
		if ($this->maximum === null) {
			$this->maximum = $this->unit->Size() * $this->calculus()->knowledge(Trading::class)->Level() * 10;
			$this->trades->setMaximum($this->maximum);
			$this->remaining = max(0, $this->maximum - $this->trades->count());
		}
		return $this->remaining;
	}

	protected function isTradePossible(): bool {
		if ($this->isTradePossible === null) {
			$castle                = $this->context->getIntelligence($this->unit->Region())->getGovernment();
			$this->isTradePossible = $castle?->Size() > Site::MAX_SIZE;
		}
		return $this->isTradePossible;
	}

	protected function goods(): Quantity {
		$this->traded->rewind();
		if ($this->traded->valid()) {
			/** @var Quantity $quantity */
			$quantity = $this->traded->current();
		} else {
			$quantity = new Quantity($this->commodity, 0);
		}
		return $quantity;
	}

	#[Pure] protected function cost(): Quantity {
		return new Quantity($this->silver, $this->cost);
	}
}
