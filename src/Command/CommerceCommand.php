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
use Lemuria\Model\Fantasya\Talent\Camouflage;
use Lemuria\Model\Fantasya\Talent\Perception;
use Lemuria\Model\Fantasya\Talent\Trading;
use Lemuria\Model\Fantasya\Unit;

/**
 * Base class for all commands that trade in a Region.
 */
abstract class CommerceCommand extends UnitCommand implements Merchant
{
	protected Resources $goods;

	protected int $demand;

	protected int $maximum;

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
	 * Determine the goods.
	 */
	protected function createGoods(): void {
		if ($this->phrase->count() !== 2) {
			throw new UnknownCommandException($this);
		}
		$demand = $this->getDemand();
		$count  = min($demand, $this->getMaximum());
		if ($count > 0) {
			$this->goods->add($demand);
		} else {
			Lemuria::Log()->debug('Merchant ' . $this . ' has no demand.');
		}
	}

	protected function getDemand(): Quantity {
		$this->demand = (int)$this->phrase->getParameter();
		$commodity    = $this->context->Factory()->commodity($this->phrase->getParameter(2));
		return new Quantity($commodity, $this->demand);
	}

	protected function getMaximum(): int {
		$this->maximum = $this->calculus()->knowledge(Trading::class)->Level() * 10;
		return $this->maximum;
	}
}
