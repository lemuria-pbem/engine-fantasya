<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Merchant;
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
			$inventory->remove(new Quantity($good, 1));
			$inventory->add(new Quantity($this->silver, $price));
			$this->count++;
			return true;
		}
		//TODO
		return false;
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
