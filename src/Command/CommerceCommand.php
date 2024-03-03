<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Activity;
use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Factory\CommerceActivityTrait;
use Lemuria\Engine\Fantasya\Factory\RealmTrait;
use Lemuria\Engine\Fantasya\Factory\SiegeTrait;
use Lemuria\Engine\Fantasya\Factory\Workload;
use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Engine\Fantasya\Message\Unit\CommerceGuardedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CommerceNotPossibleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CommerceSiegeMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Engine\Fantasya\Realm\Distributor;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Building\Site;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Party;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Relation;
use Lemuria\Model\Fantasya\Resources;
use Lemuria\Model\Fantasya\Talent;
use Lemuria\Model\Fantasya\Talent\Trading;

/**
 * Base class for all commands that trade in a Region.
 */
abstract class CommerceCommand extends UnitCommand implements Activity, Merchant
{
	use CollectTrait;
	use CommerceActivityTrait;
	use RealmTrait;
	use SiegeTrait;

	/**
	 * @type array<string>
	 */
	private const array ALL = ['alle', 'alles'];

	protected static ?Talent $trading = null;

	protected static ?Commodity $silver = null;

	protected Resources $goods;

	protected int $threshold;

	protected int $demand;

	protected ?int $maximum = null;

	protected int $remaining;

	protected int $amount;

	protected int $count = 0;

	protected int $bundle = 0;

	protected int $cost = 0;

	protected ?array $lastCheck = null;

	protected Workload $trades;

	protected Resources $traded;

	protected Commodity $commodity;

	protected ?Distributor $distributor = null;

	private ?bool $isTradePossible = null;

	/**
	 * Create a new command for given Phrase.
	 */
	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->goods  = new Resources();
		$this->traded = new Resources();
		if (!self::$trading) {
			self::$trading = self::createTalent(Trading::class);
			self::$silver  = self::createCommodity(Silver::class);
		}
	}

	public function execute(): static {
		parent::execute();
		if (!$this->isTradePossible()) {
			$this->message(CommerceNotPossibleMessage::class)->e($this->unit->Region());
			return $this;
		}
		if ($this->isSieged($this->unit->Construction())) {
			$this->message(CommerceSiegeMessage::class);
		}
		return $this;
	}

	public function canBeCentralized(): bool {
		return true;
	}

	/**
	 * Get the resources this merchant wants to trade.
	 */
	public function getGoods(): Resources {
		return $this->goods;
	}

	/**
	 * Check diplomacy between the unit and region owner and guards.
	 *
	 * This method should return the foreign parties that prevent executing the
	 * command.
	 *
	 * @return array<Party>
	 */
	public function checkBeforeCommerce(): array {
		if ($this->lastCheck === null) {
			$this->lastCheck = $this->getCheckBeforeCommerce();
			if (!empty($this->lastCheck)) {
				$this->goods->clear();
				$region = $this->unit->Region();
				foreach ($this->lastCheck as $party) {
					$this->message(CommerceGuardedMessage::class)->e($region)->e($party, CommerceGuardedMessage::PARTY);
				}
			}
		}
		return $this->lastCheck;
	}

	/**
	 * Make preparations before running the command.
	 */
	protected function initialize(): void {
		parent::initialize();
		if (!$this->checkSize() && $this->IsDefault()) {
			Lemuria::Log()->debug('Commerce command skipped due to empty unit.', ['command' => $this]);
			return;
		}

		if ($this->isRunCentrally($this)) {
			$this->trades      = $this->context->getWorkload($this->unit);
			$this->distributor = $this->createDistributor($this);
			$this->createGoods();
			Lemuria::Log()->debug('New distributor helper for realm ' . $this->distributor->Realm()->Id() . '.', ['command' => $this]);
		} else {
			if ($this->isTradePossible()) {
				$this->trades = $this->context->getWorkload($this->unit);
				$this->createGoods();
				$commerce = $this->context->getCommerce($this->unit->Region());
				if (!$this->goods->isEmpty()) {
					$commerce->register($this);
				} else {
					Lemuria::Log()->debug('Commerce registration skipped due to empty demand.', ['command' => $this]);
				}
			} else {
				Lemuria::Log()->debug('Commerce disabled in this region - no castle here.');
			}
		}
		$this->commitCommand($this);
	}

	/**
	 * The command implementation.
	 */
	protected function run(): void {
		if (count($this->goods)) {
			if ($this->distributor) {
				$this->distributor->distribute();
			} else {
				$this->context->getCommerce($this->unit->Region())->distribute($this);
			}
		}
	}

	protected function commitCommand(UnitCommand $command): void {
		if (!$this->goods->isEmpty() && !$this->checkBeforeCommerce()) {
			parent::commitCommand($command);
		}
	}

	/**
	 * Do the check before allocation.
	 *
	 * @return array<Party>
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
			$this->goods->add(new Quantity($demand->Commodity(), $this->amount));
		} else {
			Lemuria::Log()->debug('Merchant ' . $this . ' has no demand.');
		}
	}

	protected function getDemand(): Quantity {
		$n = $this->phrase->count();
		if ($n === 1) {
			$luxury          = $this->phrase->getParameter();
			$this->commodity = $this->context->Factory()->commodity($luxury);
			$demand          = $this->getMaximumSupply();
			$this->demand    = 0;
		} elseif ($n === 2) {
			$first  = $this->phrase->getParameter();
			$second = $this->phrase->getParameter(2);
			$price  = (int)$second;
			if ((string)$price === $second) {
				$this->commodity = $this->context->Factory()->commodity($first);
				$demand          = $this->commodity instanceof Luxury ? $this->calculateThreshold($price) : 0;
				$this->demand    = $demand;
			} else {
				if (in_array(strtolower($first), self::ALL)) {
					$demand       = $this->getMaximum();
					$this->demand = PHP_INT_MAX;
				} else {
					$demand       = max(0, (int)$first);
					$this->demand = $demand;
				}
				$this->commodity = $this->context->Factory()->commodity($second);
			}
		} else {
			throw new UnknownCommandException($this);
		}
		if ($this->demand === 0 && $this->commodity instanceof Luxury) {
			$this->calculateThreshold();
		}
		return new Quantity($this->commodity, $demand);
	}

	protected function getMaximum(): int {
		if ($this->maximum === null) {
			$this->maximum = $this->unit->Size() * $this->calculus()->knowledge(self::$trading)->Level() * 10;
			$this->trades->setMaximum($this->maximum);
			$this->remaining = max(0, $this->maximum - $this->trades->count());
		}
		return $this->remaining;
	}

	abstract protected function calculatePriceThresholdHere(int $price): int;

	abstract protected function calculatePriceThresholdInRealm(int $price): int;

	abstract protected function getMaximumSupplyInRealm(): int;

	abstract protected function setRealmThreshold(array $threshold): void;

	protected function getMaximumSupply(): int {
		if ($this->isRunCentrally($this)) {
			return $this->getMaximumSupplyInRealm();
		}
		return $this->getMaximumSupplyHere();
	}

	protected function getMaximumSupplyHere(): int {
		$supply = $this->context->getSupply($this->unit->Region());
		return $supply->getStep();
	}

	protected function isTradePossible(): bool {
		if ($this->isTradePossible === null) {
			$castle                = $this->context->getIntelligence($this->unit->Region())->getCastle();
			$this->isTradePossible = $castle?->Size() > Site::MAX_SIZE;
		}
		return $this->isTradePossible;
	}

	protected function calculateThreshold(int $price = 0): int {
		if ($this->distributor) {
			$this->distributor->setThreshold($price);
			return $this->calculateRealmThreshold($price);
		}
		return $this->calculateThresholdHere($price);
	}

	protected function calculateThresholdHere(int $price = 0): int {
		if ($price > 0) {
			return $this->calculatePriceThresholdHere($price);
		}
		$luxuries = $this->unit->Region()->Luxuries();
		$offer    = $luxuries->Offer();
		if ($offer->Commodity() === $this->commodity) {
			$this->threshold = $offer->Price();
		} else {
			$this->threshold = $luxuries[$this->commodity]->Price();
		}
		return 0;
	}

	protected function calculateRealmThreshold(int $price = 0): int {
		if ($price > 0) {
			return $this->calculatePriceThresholdInRealm($price);
		}
		$threshold = [];
		foreach ($this->distributor->Regions() as $region) {
			$luxuries = $region->Luxuries();
			$offer    = $luxuries->Offer();
			if ($offer->Commodity() === $this->commodity) {
				$threshold[] = $offer->Price();
			} else {
				$threshold[] = $luxuries[$this->commodity]->Price();
			}
		}
		$this->setRealmThreshold($threshold);
		return 0;
	}

	protected function goods(): Quantity {
		$this->traded->rewind();
		if ($this->traded->valid()) {
			$quantity = $this->traded->current();
		} else {
			$quantity = new Quantity($this->commodity, 0);
		}
		return $quantity;
	}

	protected function cost(): Quantity {
		return new Quantity(self::$silver, $this->cost);
	}
}
