<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Action;
use Lemuria\Engine\Fantasya\Merchant;
use Lemuria\Engine\Fantasya\Message\Unit\BuyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BuyNoneMessage;
use Lemuria\Engine\Fantasya\Message\Unit\BuyOnlyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CommerceNotPossibleMessage;
use Lemuria\Engine\Fantasya\Message\Unit\CommerceSiegeMessage;
use Lemuria\Engine\Fantasya\Statistics\StatisticsTrait;
use Lemuria\Engine\Fantasya\Statistics\Subject;
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
	use StatisticsTrait;

	/**
	 * Get the type of trade.
	 */
	public function Type(): bool {
		return Merchant::BUY;
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
			$payment = $this->collectQuantity($this->unit, $this->silver, $price);
			if ($payment->Count() === $price) {
				$inventory = $this->unit->Inventory();
				$this->traded->add(new Quantity($good, 1));
				$inventory->remove($payment);
				$this->placeDataMetrics(Subject::Purchase, $price, $this->unit);
				$inventory->add(new Quantity($good, 1));
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

	/**
	 * Finish trade, create messages.
	 */
	public function finish(): Merchant {
		if ($this->demand > 0) {
			if ($this->count < $this->demand && $this->demand < PHP_INT_MAX) {
				$this->message(BuyOnlyMessage::class)->i($this->goods())->i($this->cost(), BuyMessage::PAYMENT);
			} else {
				$this->message(BuyMessage::class)->i($this->goods())->i($this->cost(), BuyMessage::PAYMENT);
			}
		} else {
			$this->message(BuyNoneMessage::class)->s($this->goods()->Commodity());
		}
		return $this;
	}
}
