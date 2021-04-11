<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Engine\Fantasya\Message\Unit\BuyMessage;
use Lemuria\Lemuria;
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
			$payment = $this->getPayment($price);
			if ($payment->Count() === $price) {
				$inventory = $this->unit->Inventory();
				$this->traded->add(new Quantity($good, 1));
				$inventory->add(new Quantity($good, 1));
				$inventory->remove($payment);
				$this->count++;
				$this->cost += $price;
				return true;
			}
		}
		return false;
	}

	/**
	 * Give a cost estimation to the merchant to allow silver reservation from pool.
	 */
	public function costEstimation(int $cost): Merchant {
		$payment = new Quantity($this->silver, $cost);
		Lemuria::Log()->debug('Merchant ' . $this . ' expects buy cost of ' . $payment . '.');
		$this->context->getResourcePool($this->unit)->reserve($this->unit, $payment);
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
					$this->message(BuyMessage::class)->i($this->goods())->i($this->cost(), BuyMessage::PAYMENT);
				}
			}
		} else {
			//TODO no demand
		}
	}

	private function getPayment(int $price): Quantity {
		$payment   = new Quantity($this->silver, $price);
		$inventory = $this->unit->Inventory();
		$reserve   = $inventory[$this->silver];
		if ($reserve->Count() >= $price) {
			return $payment;
		}
		return $this->context->getResourcePool($this->unit)->reserve($this->unit, $payment);
	}
}
