<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Quantity;

/**
 * Buy goods on the market.
 *
 * KAUFEN [<amount>] <commodity>
 */
final class Buy extends CommerceCommand
{
	/**
	 * Get the type of trade.
	 */
	public function Type(): bool {
		return Merchant::BUY;
	}

	public function trade(Luxury $good, int $price): bool {
		if ($this->count < $this->amount) {
			$payment = new Quantity($this->silver, $price);
			$payment = $this->context->getResourcePool($this->unit)->reserve($this->unit, $payment);
			if ($payment->Count() === $price) {
				$inventory = $this->unit->Inventory();
				$inventory->add(new Quantity($good, 1));
				$inventory->remove($payment);
				$this->count++;
				return true;
			}
		}
		//TODO
		return false;
	}
}
