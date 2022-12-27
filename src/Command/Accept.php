<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Sales;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptDemandAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptDemandAmountMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptDemandPriceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptFeePaidMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptFeeReceivedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptNoFeeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptNoFeeReceivedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptOfferAlreadyMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptDemandMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptDemandRemovedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptForbiddenTradeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptNoDeliveryMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptNoMarketMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptNoPaymentMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptNoTradeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptBoughtMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptOfferAmountMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptOfferMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptOfferPriceMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptOfferRemovedMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptSoldMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptUnsatisfiableTradeMessage;
use Lemuria\Exception\ItemException;
use Lemuria\Exception\ItemSetException;
use Lemuria\Exception\LemuriaException;
use Lemuria\Id;
use Lemuria\Lemuria;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Exception\SalesException;
use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Market\Deal;
use Lemuria\Model\Fantasya\Market\Sales as SalesModel;
use Lemuria\Model\Fantasya\Market\Trade;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * Accept a trade from a unit.
 *
 * - HANDEL <trade>
 * - HANDEL <trade> <amount> <commodity>
 * - HANDEL <trade> <price> <payment>
 * - HANDEL <trade> <amount> <commodity> <price> <payment>
 */
final class Accept extends UnitCommand
{
	use CollectTrait;

	/**
	 * array<int, Market>
	 */
	protected array $market = [];

	/**
	 * array<int, Sales>
	 */
	protected array $sales = [];

	protected ?int $index = null;

	protected ?Id $id = null;

	protected ?Trade $trade = null;

	protected ?int $status = null;

	protected ?int $amount = null;

	protected ?int $price = null;

	protected function initialize(): void {
		parent::initialize();
		foreach ($this->unit->Region()->Estate() as $construction /* @var Construction $construction */) {
			$extensions = $construction->Extensions();
			if ($extensions->offsetExists(Market::class)) {
				/** @var Market $market */
				$market         = $extensions[Market::class];
				$this->market[] = $market;
				$this->sales[]  = new Sales($construction);
			}
		}
		$this->parseTrade();
	}

	protected function run(): void {
		if (empty($this->market)) {
			$this->message(AcceptNoMarketMessage::class);
			return;
		}
		if (!$this->trade) {
			$closedTrades = $this->context->getClosedTrades();
			/** @var Trade $closed */
			$closed = $closedTrades->has($this->id) ? $closedTrades->offsetGet($this->id) : null;
			if ($closed) {
				if ($closed->Trade() === Trade::OFFER) {
					$this->message(AcceptOfferAlreadyMessage::class)->p((string)$this->id);
				} else {
					$this->message(AcceptDemandAlreadyMessage::class)->p((string)$this->id);
				}
				return;
			}
			$this->message(AcceptNoTradeMessage::class)->p((string)$this->id);
			return;
		}
		if ($this->status === SalesModel::FORBIDDEN) {
			$this->message(AcceptForbiddenTradeMessage::class)->e($this->trade);
		} elseif ($this->status === SalesModel::UNSATISFIABLE && !$this->context->getTurnOptions()->IsSimulation()) {
			$merchant = $this->trade->Unit();
			$this->message(AcceptUnsatisfiableTradeMessage::class, $this->unit)->e($this->trade)->e($merchant, AcceptUnsatisfiableTradeMessage::MERCHANT);
		} else {
			$this->parseParameters();
			if ($this->trade->Goods()->IsVariable()) {
				if ($this->trade->Price()->IsVariable()) {
					$this->acceptPiecesBargain();
				} else {
					$this->acceptPieces();
				}
			} else {
				if ($this->trade->Price()->IsVariable()) {
					$this->acceptBargain();
				} else {
					$this->accept();
				}
			}
		}
	}

	private function accept(): void {
		if ($this->amount !== null || $this->price !== null) {
			throw new InvalidCommandException($this);
		}

		$price   = $this->trade->Price();
		$payment = $this->collectPayment($price->Commodity(), $price->Amount());
		if ($payment) {
			$goods    = $this->trade->Goods();
			$quantity = new Quantity($goods->Commodity(), $goods->Amount());
			$this->exchange($quantity, $payment);
			$this->tradeMessages($quantity, $payment);
		}
	}

	private function acceptPieces(): void {
		if ($this->amount === null || $this->amount <= 0 || $this->price !== null) {
			throw new InvalidCommandException($this);
		}

		$goods = $this->checkPieces();
		if ($goods) {
			$price   = $this->trade->Price();
			$payment = $this->collectPayment($price->Commodity(), $this->amount * $price->Amount());
			if ($payment) {
				$quantity = new Quantity($goods->Commodity(), $this->amount);
				$this->exchange($quantity, $payment);
				$this->tradeMessages($quantity, $payment);
			}
		}
	}

	private function acceptBargain(): void {
		if ($this->amount !== null || $this->price === null) {
			throw new InvalidCommandException($this);
		}

		$price = $this->trade->Price();
		if ($this->price < $price->Minimum()) {
			if ($this->trade->Trade() === Trade::OFFER) {
				$this->message(AcceptOfferPriceMessage::class)->e($this->trade)->e($this->trade->Unit(), AcceptOfferAmountMessage::UNIT);
			} else {
				$this->message(AcceptDemandPriceMessage::class)->e($this->trade)->e($this->trade->Unit(), AcceptOfferAmountMessage::UNIT);
			}
			return;
		}

		$payment = $this->collectPayment($price->Commodity(), $this->price);
		if ($payment) {
			$goods    = $this->trade->Goods();
			$quantity = new Quantity($goods->Commodity(), $goods->Amount());
			$this->exchange($quantity, $payment);
			$this->tradeMessages($quantity, $payment);
		}
	}

	private function acceptPiecesBargain(): void {
		if ($this->amount === null || $this->price === null) {
			throw new InvalidCommandException($this);
		}

		$goods = $this->checkPieces();
		if ($goods) {
			$price   = $this->trade->Price();
			$minimum = $this->amount * $price->Minimum();
			if ($this->price < $minimum) {
				if ($this->trade->Trade() === Trade::OFFER) {
					$this->message(AcceptOfferPriceMessage::class)->e($this->trade)->e($this->trade->Unit(), AcceptOfferAmountMessage::UNIT);
				} else {
					$this->message(AcceptDemandPriceMessage::class)->e($this->trade)->e($this->trade->Unit(), AcceptOfferAmountMessage::UNIT);
				}
				return;
			}

			$payment = $this->collectPayment($price->Commodity(), $this->price);
			if ($payment) {
				$quantity = new Quantity($goods->Commodity(), $this->amount);
				$this->exchange($quantity, $payment);
				$this->tradeMessages($quantity, $payment);
			}
		}
	}

	private function parseTrade(): void {
		if ($this->phrase->count() <= 0) {
			throw new InvalidCommandException($this);
		}
		$this->id = Id::fromId($this->phrase->getParameter());
		try {
			$trade = Trade::get($this->id);
			foreach ($this->sales as $index => $sales /* @var Sales $sales */) {
				if ($sales->has($trade)) {
					$this->index  = $index;
					$this->status = $sales->getStatus($trade);
					$this->trade  = $trade;
				}
			}
		} catch (NotRegisteredException|SalesException) {
		}
	}

	private function parseParameters(): void {
		$goods = $this->trade->Goods();
		$price = $this->trade->Price();
		$n     = $this->phrase->count();

		if ($n > 1) {
			$parameter = $this->phrase->getParameter(2);
			$number    = (int)$parameter;
			if ((string)$number !== $parameter) {
				throw new InvalidCommandException($this);
			}
			$i         = 3;
			$commodity = $this->parseCommodity($i, $n);

			if ($i < $n) {
				if ($i + 1 < $n) {
					$this->amount = $number;
					if ($commodity !== $goods->Commodity()) {
						throw new InvalidCommandException($this);
					}

					$parameter = $this->phrase->getParameter($i++);
					$number    = (int)$parameter;
					if ((string)$number !== $parameter) {
						throw new InvalidCommandException($this);
					}
					$this->price = $number;

					$payment = $this->parseCommodity($i, $n);
					if ($i <= $n) {
						throw new InvalidCommandException($this);
					}
					if ($payment !== $price->Commodity()) {
						throw new InvalidCommandException($this);
					}
				} else {
					throw new InvalidCommandException($this);
				}
			} else {
				if ($goods->IsVariable()) {
					if ($price->IsVariable()) {
						throw new InvalidCommandException($this);
					}
					if ($commodity === $goods->Commodity()) {
						$this->amount = $number;
						return;
					}
				}
				if ($price->IsVariable()) {
					if ($commodity === $price->Commodity()) {
						$this->price = $number;
						return;
					}
				}
				throw new InvalidCommandException($this);
			}
		}
	}

	private function parseCommodity(int &$index, int $last): Commodity {
		$i = $index;
		while ($index < $last) {
			$parameter = $this->phrase->getParameter(++$index);
			$number    = (int)$parameter;
			if ((string)$number === $parameter) {
				break;
			}
		}
		$commodity = $this->phrase->getLine($i, $index - 1);
		return $this->context->Factory()->commodity($commodity);
	}

	private function checkPieces(): ?Deal {
		$goods   = $this->trade->Goods();
		$maximum = $goods->IsAdapting() ? $this->getAvailableMaximum(): $goods->Maximum();
		if ($this->amount < $goods->Minimum() || $this->amount > $maximum) {
			if ($this->trade->Trade() === Trade::OFFER) {
				$this->message(AcceptOfferAmountMessage::class)->e($this->trade)->e($this->trade->Unit(), AcceptOfferAmountMessage::UNIT);
			} else {
				$this->message(AcceptDemandAmountMessage::class)->e($this->trade)->e($this->trade->Unit(), AcceptOfferAmountMessage::UNIT);
			}
			return null;
		}
		return $goods;
	}

	private function collectPayment(Commodity $commodity, int $price): ?Quantity {
		$payment = $this->collectQuantity($this->unit, $commodity, $price);
		if ($payment->Count() < $price) {
			if ($this->trade->Trade() === Trade::OFFER) {
				$this->message(AcceptNoPaymentMessage::class)->s($commodity)->e($this->trade);
			} else {
				$this->message(AcceptNoDeliveryMessage::class)->s($commodity)->e($this->trade);
			}
			return null;
		}
		return $payment;
	}

	private function exchange(Quantity $quantity, Quantity $payment): void {
		if ($this->trade->Trade() === Trade::DEMAND) {
			$temp     = $quantity;
			$quantity = $payment;
			$payment  = $temp;
		}
		$unit     = $this->trade->Unit();
		$merchant = $unit->Inventory();
		$customer = $this->unit->Inventory();
		if (!$this->context->getTurnOptions()->IsSimulation()) {
			try {
				$merchant->remove($quantity);
			} catch (ItemException|ItemSetException $e) {
				throw new LemuriaException(previous: $e);
			}
			$customer->remove($payment);
			$merchant->add(new Quantity($payment->Commodity(), $payment->Count()));
			$customer->add(new Quantity($quantity->Commodity(), $quantity->Count()));
		}

		$fee = $this->market[$this->index]->Fee();
		if (is_float($fee) && $fee > 0.0) {
			$this->payFee($unit, $payment, $fee);
		}

		if (!$this->trade->IsRepeat() && !$this->context->getTurnOptions()->IsSimulation()) {
			Lemuria::Catalog()->reassign($this->trade);
			$unit->Trades()->remove($this->trade);
			Lemuria::Catalog()->remove($this->trade);
			if ($this->trade->Trade() === Trade::OFFER) {
				$this->message(AcceptOfferRemovedMessage::class, $unit)->e($this->trade);
			} else {
				$this->message(AcceptDemandRemovedMessage::class, $unit)->e($this->trade);
			}
		}
	}

	private function tradeMessages(Quantity $quantity, Quantity $payment): void {
		if ($this->trade->Trade() === Trade::OFFER) {
			$this->offerMessages($quantity, $payment);
		} else {
			$this->demandMessages($quantity, $payment);
		}
	}

	private function offerMessages(Quantity $quantity, Quantity $payment): void {
		$trade    = $this->trade;
		$merchant = $this->trade->Unit();
		$customer = $this->unit;
		$unit     = AcceptOfferMessage::UNIT;
		$pay      = AcceptOfferMessage::PAYMENT;
		$this->message(AcceptOfferMessage::class, $merchant)->e($trade)->e($customer, $unit)->i($quantity)->i($payment, $pay);
		$this->message(AcceptBoughtMessage::class)->e($trade)->e($merchant, $unit)->i($quantity)->i($payment, $pay);
	}

	private function demandMessages(Quantity $quantity, Quantity $payment): void {
		$trade    = $this->trade;
		$merchant = $this->trade->Unit();
		$customer = $this->unit;
		$unit     = AcceptOfferMessage::UNIT;
		$pay      = AcceptOfferMessage::PAYMENT;
		$this->message(AcceptDemandMessage::class, $merchant)->e($trade)->e($customer, $unit)->i($quantity)->i($payment, $pay);
		$this->message(AcceptSoldMessage::class)->e($trade)->e($merchant, $unit)->i($quantity)->i($payment, $pay);
	}

	private function payFee(Unit $unit, Quantity $payment, float $rate) {
		$fee   = (int)round($rate * $payment->Count());
		$owner = $this->unit->Construction()->Inhabitants()->Owner();
		if ($fee > 0) {
			$quantity = new Quantity($payment->Commodity(), $fee);
			$unit->Inventory()->remove($quantity);
			$owner->Inventory()->add(new Quantity($payment->Commodity(), $fee));
			$this->message(AcceptFeePaidMessage::class)->e($owner)->i($quantity);
			$this->message(AcceptFeeReceivedMessage::class, $owner)->e($this->unit)->i($quantity);
		} else {
			$this->message(AcceptNoFeeMessage::class)->e($this->trade);
			$this->message(AcceptNoFeeReceivedMessage::class, $owner)->e($this->trade)->e($this->unit, AcceptNoFeeReceivedMessage::UNIT);
		}
	}

	private function getAvailableMaximum(): int {
		$inventory = $this->trade->Unit()->Inventory();
		if ($this->trade->Trade() === Trade::OFFER) {
			$commodity = $this->trade->Goods()->Commodity();
			return $inventory[$commodity]->Count();
		} else {
			$price     = $this->trade->Price();
			$commodity = $price->Commodity();
			$ppp       = $price->Maximum();
			return (int)floor($inventory[$commodity]->Count() / $ppp);
		}
	}
}
