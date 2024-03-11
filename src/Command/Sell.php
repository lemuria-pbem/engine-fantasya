<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Factory\Supply;
use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Engine\Fantasya\Message\Unit\BuyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SellMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SellNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SellOnlyMessage;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Region;

/**
 * Sell goods on the market.
 *
 * - VERKAUFEN <commodity>
 * - VERKAUFEN [<amount>] <commodity>
 * - VERKAUFEN Alle|Alles <commodity>
 * - VERKAUFEN <commodity> <min. price>
 */
final class Sell extends CommerceCommand
{
	public const float QUOTA = 0.05;

	protected int $threshold = 0;

	/**
	 * Get the type of trade.
	 */
	public function Type(): bool {
		return Merchant::SELL;
	}

	public function trade(Luxury $good, int $price): bool {
		if ($this->count < $this->amount && $price >= $this->threshold) {
			$luxury = $this->collectQuantity($this->unit, $good, 1);
			if ($luxury->Count() > 0) {
				$inventory = $this->unit->Inventory();
				$this->traded->add($luxury);
				$inventory->remove(new Quantity($good, 1));
				$inventory->add(new Quantity(parent::$silver, $price));
				$this->count++;
				$this->bundle++;
				$this->cost += $price;
				return true;
			}
		}
		return false;
	}

	/**
	 * Give a cost estimation to the merchant to allow silver reservation from pool.
	 */
	public function costEstimation(int $cost): static {
		return $this;
	}

	/**
	 * Finish trade, create messages.
	 */
	public function finish(Region $region): static {
		$bundle = new Quantity($this->goods()->Commodity(), $this->bundle);
		if ($this->distributor) {
			if ($this->bundle > 0) {
				$this->message(SellMessage::class)->e($region)->i($bundle)->i($this->cost(), BuyMessage::PAYMENT);
			}
			$this->traded->clear();
		} else {
			if ($this->count > 0) {
				if ($this->demand > 0 && $this->count < $this->demand && $this->demand < PHP_INT_MAX) {
					$this->message(SellOnlyMessage::class)->e($region)->i($bundle)->i($this->cost(), BuyMessage::PAYMENT);
				} else {
					$this->message(SellMessage::class)->e($region)->i($bundle)->i($this->cost(), BuyMessage::PAYMENT);
				}
			} else {
				$this->message(SellNoneMessage::class)->e($region)->s($this->goods()->Commodity());
			}
		}
		$this->bundle = 0;
		$this->cost   = 0;
		return $this;
	}

	protected function calculatePriceThresholdHere(int $price): int {
		/** @var Luxury $luxury */
		$luxury = $this->commodity;
		$supply = $this->context->getSupply($this->unit->Region(), $luxury);
		return $supply->calculate($price, Supply::PRICE_MINIMUM);
	}

	protected function calculatePriceThresholdInRealm(int $price): int {
		$total = 0;
		foreach ($this->distributor->Regions() as $region) {
			/** @var Luxury $luxury */
			$luxury = $this->commodity;
			if ($region->Luxuries()?->offsetExists($luxury)) {
				$amount = $this->context->getSupply($region, $luxury)->calculate($price, Supply::PRICE_MINIMUM);
				$total += $amount;
			}
		}
		return $total;
	}

	protected function getDemand(): Quantity {
		$demand = parent::getDemand();
		if ($demand->Count() > 0 && $this->threshold <= 0) {
			$demand = $this->collectQuantity($this->unit, $demand->Commodity(), $demand->Count());
		}
		return $demand;
	}

	protected function getMaximumSupplyInRealm(): int {
		$maximum = 0;
		foreach ($this->distributor->Regions() as $region) {
			/** @var Luxury $luxury */
			$luxury = $this->commodity;
			if ($region->Luxuries()?->offsetExists($luxury)) {
				$maximum += $this->context->getSupply($region, $luxury)->getStep();
			}
		}
		return $maximum;
	}

	protected function setRealmThreshold(array $threshold): void {
		$this->threshold = max($threshold);
	}
}
