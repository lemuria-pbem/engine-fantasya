<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Engine\Fantasya\Message\Unit\SellMessage;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Quantity;

/**
 * Sell goods on the market.
 *
 * VERKAUFEN [<amount>] <commodity>
 */
final class Sell extends CommerceCommand
{
	private int $reserve;

	/**
	 * Get the type of trade.
	 */
	public function Type(): bool {
		return Merchant::SELL;
	}

	public function trade(Luxury $good, int $price): bool {
		if ($this->count < $this->amount) {
			$inventory = $this->unit->Inventory();
			$this->traded->add(new Quantity($good, 1));
			$inventory->remove(new Quantity($good, 1));
			$inventory->add(new Quantity($this->silver, $price));
			$this->count++;
			$this->cost += $price;
			return true;
		}
		return false;
	}

	/**
	 * Give a cost estimation to the merchant to allow silver reservation from pool.
	 */
	public function costEstimation(int $cost): Merchant {
		$income = new Quantity($this->silver, $cost);
		Lemuria::Log()->debug('Merchant ' . $this . ' expects income of ' . $income . '.');
		return $this;
	}

	protected function run(): void {
		parent::run();
		if ($this->demand > 0) {
			if ($this->remaining < $this->demand) {
				//TODO only left
			} else {
				if ($this->count < $this->demand) {
					//TODO only
				} else {
					$this->message(SellMessage::class)->i($this->goods())->i($this->cost(), SellMessage::PAYMENT);
				}
			}
		} else {
			//TODO no demand
		}
	}

	protected function getDemand(): Quantity {
		$demand = parent::getDemand();
		if ($demand->Count() > 0) {
			$demand        = $this->context->getResourcePool($this->unit)->reserve($this->unit, $demand);
			$this->reserve = $demand->Count();
		}
		return $demand;
	}
}
