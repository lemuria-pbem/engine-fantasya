<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Engine\Fantasya\Message\Unit\BuyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CommerceNotPossibleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CommerceSiegeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SellMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SellNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\SellOnlyMessage;
use Lemuria\Model\Fantasya\Luxury;
use Lemuria\Model\Fantasya\Quantity;

/**
 * Sell goods on the market.
 *
 * VERKAUFEN [<amount>] <commodity>
 */
final class Sell extends CommerceCommand
{
	/**
	 * Get the type of trade.
	 */
	public function Type(): bool {
		return Merchant::SELL;
	}

	public function execute(): Action {
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
		// $income = new Quantity($this->silver, $cost);
		// Lemuria::Log()->debug('Merchant ' . $this . ' expects income of ' . $income . '.');
		return $this;
	}

	/**
	 * Finish trade, create messages.
	 */
	public function finish(): Merchant {
		if ($this->demand > 0) {
			if ($this->count < $this->demand && $this->demand < PHP_INT_MAX) {
				$this->message(SellOnlyMessage::class)->i($this->goods())->i($this->cost(), BuyMessage::PAYMENT);
			} else {
				$this->message(SellMessage::class)->i($this->goods())->i($this->cost(), BuyMessage::PAYMENT);
			}
		} else {
			$this->message(SellNoneMessage::class)->s($this->goods()->Commodity());
		}
		return $this;
	}

	protected function getDemand(): Quantity {
		$demand = parent::getDemand();
		if ($demand->Count() > 0) {
			$demand = $this->collectQuantity($this->unit, $demand->Commodity(), $demand->Count());
		}
		return $demand;
	}
}
