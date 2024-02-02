<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Factory\Supply;
use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Engine\Fantasya\Message\Unit\BuyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BuyNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BuyOnlyMessage;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Quantity;

/**
 * Buy goods on the market.
 *
 * - KAUFEN <commodity>
 * - KAUFEN <amount> <commodity>
 * - KAUFEN Alle|Alles <commodity>
 * - KAUFEN <commodity> <max. price>
 */
final class Buy extends CommerceCommand
{
	use StatisticsTrait;

	protected int $threshold = PHP_INT_MAX;

	/**
	 * Get the type of trade.
	 */
	public function Type(): bool {
		return Merchant::BUY;
	}

	public function trade(Luxury $good, int $price): bool {
		if ($this->count < $this->amount && $price <= $this->threshold) {
			$payment = $this->collectQuantity($this->unit, parent::$silver, $price);
			if ($payment->Count() === $price) {
				$inventory = $this->unit->Inventory();
				$this->traded->add(new Quantity($good, 1));
				$inventory->remove($payment);
				$this->placeDataMetrics(Subject::Purchase, $price, $this->unit);
				$inventory->add(new Quantity($good, 1));
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
		$payment = new Quantity(parent::$silver, $cost);
		$this->context->getResourcePool($this->unit)->reserve($this->unit, $payment);
		return $this;
	}

	/**
	 * Finish trade, create messages.
	 */
	public function finish(): static {
		if ($this->count > 0) {
			$bundle = new Quantity($this->goods()->Commodity(), $this->bundle);
			if ($this->demand > 0 && $this->count < $this->demand && $this->demand < PHP_INT_MAX) {
				$this->message(BuyOnlyMessage::class)->i($bundle)->i($this->cost(), BuyMessage::PAYMENT);
			} else {
				$this->message(BuyMessage::class)->i($bundle)->i($this->cost(), BuyMessage::PAYMENT);
			}
		} else {
			$this->message(BuyNoneMessage::class)->s($this->goods()->Commodity());
		}
		$this->bundle = 0;
		$this->cost   = 0;
		return $this;
	}

	protected function calculatePriceThresholdHere(int $price): int {
		/** @var Luxury $luxury */
		$luxury = $this->commodity;
		$supply = $this->context->getSupply($this->unit->Region(), $luxury);
		return $supply->calculate($price, Supply::PRICE_MAXIMUM);
	}

	protected function calculatePriceThresholdInRealm(int $price): int {
		$total = 0;
		foreach ($this->distributor->Regions() as $region) {
			/** @var Luxury $luxury */
			$luxury = $this->commodity;
			if ($region->Luxuries()?->offsetExists($luxury)) {
				$amount = $this->context->getSupply($region, $luxury)->calculate($price, Supply::PRICE_MAXIMUM);
				$total += $amount;
			}
		}
		return $total;
	}

	protected function getMaximumSupplyInRealm(): int {
		$maximum = 0;
		foreach ($this->distributor->Regions() as $region) {
			if ($region->Luxuries()?->Offer()->Commodity() === $this->commodity) {
				$maximum += $this->context->getSupply($region, $this->commodity)->getStep();
			}
		}
		return $maximum;
	}

	protected function setRealmThreshold(array $threshold): void {
		$this->threshold = min($threshold);
	}
}
