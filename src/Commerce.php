<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya;

use Lemuria\Engine\Fantasya\Command\Sell;
use Lemuria\Engine\Fantasya\Exception\CommerceException;
use Lemuria\Engine\Fantasya\Factory\CommandPriority;
use Lemuria\Engine\Fantasya\Factory\Supply;
use Lemuria\Engine\Fantasya\Factory\Workload;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;
use Lemuria\Model\Fantasya\Unit;

/**
 * Helper for trading distribution.
 */
final class Commerce
{
	use BuilderTrait;

	private readonly CommandPriority $priority;

	/**
	 * @var array<int, Merchant>
	 */
	private array $merchants = [];

	/**
	 * @var array<int, bool>
	 */
	private array $merchantsLeft = [];

	/**
	 * @var array<string, array>
	 */
	private array $goods = [];

	/**
	 * @var array<string, Supply>
	 */
	private array $supplies = [];

	/**
	 * @var array<int, array>
	 */
	private array $rounds = [];

	private int $round = 0;

	private bool $resetGoods = false;

	private readonly Commodity $silver;

	private int $regionSilver;

	public function __construct(private readonly Region $region) {
		// Lemuria::Log()->debug('New commerce helper for region ' . $region->Id() . '.', ['commerce' => $this]);
		$this->priority = CommandPriority::getInstance();
		$this->silver   = self::createCommodity(Silver::class);
	}

	public function Region(): Region {
		return $this->region;
	}

	/**
	 * Register a Merchant.
	 */
	public function register(Merchant $merchant): Commerce {
		if ($this->resetGoods) {
			$this->goods      = [];
			$this->resetGoods = false;
			// Lemuria::Log()->debug('Goods have been reset in commerce of region ' . $this->region->Id() . '.');
		}

		$id                           = $merchant->getId();
		$priority                     = $this->priority->getPriority($merchant);
		$this->rounds[$priority][$id] = $id;
		$this->merchants[$id]         = $merchant;
		$this->merchantsLeft[$id]     = true;
		Lemuria::Log()->debug('Merchant #' . $id . ' registered for region ' . $this->region->Id() .'.', ['merchant' => $merchant]);
		return $this;
	}

	/**
	 * Start resource distribution.
	 */
	public function distribute(Merchant $merchant): void {
		$id = $merchant->getId();
		if (!isset($this->merchants[$id])) {
			throw new CommerceException($merchant, $this->region);
		}
		if ($merchant->checkBeforeCommerce()) {
			$this->unregister($merchant);
		}
		unset($this->merchantsLeft[$id]);

		$round = $this->priority->getPriority($merchant);
		if ($round > $this->round) {
			if (empty($this->merchantsLeft)) {
				$this->analyze($round);
				foreach (array_keys($this->goods) as $class) {
					$this->trade($class);
				}
				$this->finish($round);
				$this->round = $round;
			}
		}
		$this->resetGoods = true;
	}

	/**
	 * @return array<Supply>
	 */
	public function getSupplies(): array {
		return $this->supplies;
	}

	protected function getWorkload(Unit $unit): Workload {
		return State::getInstance()->getWorkload($unit);
	}

	/**
	 * Remove a Consumer.
	 */
	private function unregister(Merchant $merchant): Commerce {
		$id       = $merchant->getId();
		$priority = $this->priority->getPriority($merchant);
		unset($this->rounds[$priority][$id]);
		unset($this->merchants[$id]);
		Lemuria::Log()->debug('Merchant #' . $id . ' unregistered for region ' . $this->Region()->Id() . '.');
		return $this;
	}

	/**
	 * Analyze the demand.
	 */
	private function analyze(int $round): void {
		foreach ($this->rounds[$round] as $id) {
			$merchant = $this->merchants[$id];
			foreach ($merchant->getGoods() as $class => $quantity) {
				$luxury = $quantity->Commodity();
				if ($luxury instanceof Luxury) {
					$demand = $quantity->Count();
					if (!isset($this->goods[$class])) {
						$supply              = State::getInstance()->getSupply($this->region, $luxury);
						$this->goods[$class] = ['demand' => [], 'good' => $luxury, 'supply' => $supply];
					}
					$this->goods[$class]['demand'][$id] = $demand;
				}
			}
		}
		$resources          = $this->region->Resources();
		$this->regionSilver = (int)floor(Sell::QUOTA * $resources[$this->silver]->Count());
		Lemuria::Log()->debug('The peasants have ' . $this->regionSilver . ' left for trading.');
	}

	/**
	 * Trade a good one-by-one with all merchants.
	 */
	private function trade(string $class): void {
		Lemuria::Log()->debug('Trading ' . $class . ' between merchants and peasants in region ' . $this->region . '.');
		/** @var Supply $supply */
		$supply                 = $this->goods[$class]['supply'];
		$this->supplies[$class] = $supply;
		/** @var Luxury $good */
		$good       = $this->goods[$class]['good'];
		$demand     = $this->goods[$class]['demand'];
		$merchants  = Lemuria::Random()->shuffleArray(array_keys($demand));
		$total      = array_sum($demand);
		$estimation = $supply->estimate($total);
		Lemuria::Log()->debug(count($demand) . ' merchants want to trade ' . $total . ' ' . $class . ' (est. cost: ' . $estimation . ').');
		Lemuria::Log()->debug('The peasants will trade up to ' . $supply->count() . ' ' . $class . '.');
		foreach ($demand as $id => $count) {
			$merchant = $this->merchants[$id];
			$merchant->costEstimation((int)ceil($count / $total * $estimation));
		}

		$i      = 0;
		$n      = count($merchants);
		$price  = 0;
		$isOpen = false;
		$traded = 0;
		while ($n > 0 && ($isOpen || $supply->hasMore())) {
			if (!$isOpen) {
				$price  = $supply->ask();
				$isOpen = true;
			}
			$id       = $merchants[$i];
			$merchant = $this->merchants[$id];
			if (!$merchant->Type() === Merchant::SELL && $this->regionSilver < $price) {
				Lemuria::Log()->debug('The peasants have no more silver to buy luxuries.');
				break;
			}
			$trades = $this->getWorkload($merchant->Unit());
			if ($trades->CanWork() && $this->tradeOne($merchant, $good, $price)) {
				$supply->one();
				$trades->add();
				$isOpen = false;
				$i++;
				$traded++;
			} else {
				if (!$trades->CanWork()) {
					Lemuria::Log()->debug('Merchant ' . $merchant . ' has no more trades.');
				}
				unset($merchants[$i]);
				$merchants = array_values($merchants);
				$n--;
			}
			if ($i >= $n) {
				$i = 0;
			}
		}
		if (!$supply->hasMore()) {
			Lemuria::Log()->debug('No more peasants want to trade ' . $class . '.');
		}
		Lemuria::Log()->debug($traded . ' ' . $class . ' were traded in region ' . $this->region . '.');
		Lemuria::Log()->debug('The peasants have ' . $this->regionSilver . ' silver now.');
	}

	private function tradeOne(Merchant $merchant, Luxury $good, int $price): bool {
		if ($merchant->trade($good, $price)) {
			if ($merchant->Type() === Merchant::BUY) {
				$this->region->Resources()->add(new Quantity($this->silver, $price));
				$this->regionSilver += $price;
			} else {
				$this->region->Resources()->remove(new Quantity($this->silver, $price));
				$this->regionSilver -= $price;
			}
			return true;
		}
		return false;
	}

	private function finish(int $round): void {
		foreach ($this->rounds[$round] as $id) {
			$merchant = $this->merchants[$id];
			$merchant->finish($this->region);
		}
	}
}
