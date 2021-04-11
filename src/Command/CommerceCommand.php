<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use JetBrains\PhpStorm\Pure;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Lemuria;
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
abstract class CommerceCommand extends UnitCommand implements Merchant
{
	protected Resources $goods;

	protected int $demand;

	protected int $maximum;

	protected int $amount;

	protected int $count = 0;

	protected ?array $lastCheck = null;

	protected Commodity $silver;

	/**
	 * Create a new command for given Phrase.
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->goods  = new Resources();
		$this->silver = self::createCommodity(Silver::class);
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
		$this->createGoods();
		if (count($this->goods)) {
			$this->context->getCommerce($this->unit->Region())->register($this);
		} else {
			Lemuria::Log()->debug('Commerce registration skipped due to empty demand.', ['command' => $this]);
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
			$this->demand = PHP_INT_MAX;
			$luxury       = $this->phrase->getParameter();
		} elseif ($n === 2) {
			$this->demand = (int)$this->phrase->getParameter();
			$luxury       = $this->phrase->getParameter(2);
		} else {
			throw new UnknownCommandException($this);
		}
		$commodity = $this->context->Factory()->commodity($luxury);
		return new Quantity($commodity, $this->demand);
	}

	protected function getMaximum(): int {
		$this->maximum = $this->calculus()->knowledge(Trading::class)->Level() * 10;
		return $this->maximum;
	}
}
