<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Effect\TradeEffect;
use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Factory\CollectTrait;
use Lemuria\Engine\Fantasya\Factory\Model\Sales;
use Lemuria\Engine\Fantasya\Factory\UnicumTrait;
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
use Lemuria\Engine\Fantasya\Message\Unit\AcceptOfferReserveMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptOfferUnableMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptSoldMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptTradeUnableMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptUnsatisfiableTradeMessage;
use Lemuria\Engine\Fantasya\State;
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
use Lemuria\Model\Fantasya\Party\Type;
use Lemuria\Model\Fantasya\Quantity;
use Lemuria\Model\Fantasya\Unit;

/**
 * Accept a trade from a unit.
 *
 * - HANDEL <trade>
 * - HANDEL <trade> *|Alle|Alles
 * - HANDEL <trade> <amount>
 * - HANDEL <trade> <min>-<max>
 * - HANDEL <trade> <min>-<max> <commodity>
 * - HANDEL <trade> <amount> <commodity>
 * - HANDEL <trade> <price> <payment>
 * - HANDEL <trade> <amount> <commodity> <price> <payment>
 */
final class Accept extends UnitCommand
{
	use CollectTrait;
	use UnicumTrait;

	/**
	 * array<int, Market>
	 */
	protected array $market = [];

	/**
	 * array<int, Construction>
	 */
	protected array $sales = [];

	protected ?int $index = null;

	protected ?Id $id = null;

	protected ?Trade $trade = null;

	protected ?int $status = null;

	protected ?int $amount = null;

	protected ?array $range = null;

	protected ?int $price = null;

	protected function initialize(): void {
		parent::initialize();
		foreach ($this->unit->Region()->Estate() as $construction) {
			$extensions = $construction->Extensions();
			if ($extensions->offsetExists(Market::class)) {
				/** @var Market $market */
				$market         = $extensions[Market::class];
				$this->market[] = $market;
				$this->sales[]  = $construction;
			}
		}
	}

	protected function run(): void {
		if (empty($this->market)) {
			if ($this->buyUnicumFromMerchant()) {
				return;
			}
			if (!$this->parseMerchantTrade()) {
				$this->message(AcceptNoMarketMessage::class, $this->unit);
				return;
			}
		} else {
			$this->parseTrade();
		}

		if (!$this->trade) {
			$closed = $this->context->getClosedTrades()[$this->id->Id()] ?? null;
			if ($closed) {
				if ($closed->Trade() === Trade::OFFER) {
					$this->message(AcceptOfferAlreadyMessage::class, $this->unit)->p((string)$this->id);
				} else {
					$this->message(AcceptDemandAlreadyMessage::class, $this->unit)->p((string)$this->id);
				}
				return;
			}
			if ($this->buyUnicumFromMerchant()) {
				return;
			}
			$this->message(AcceptNoTradeMessage::class, $this->unit)->p((string)$this->id);
			return;
		}

		if ($this->status === SalesModel::FORBIDDEN) {
			$this->message(AcceptForbiddenTradeMessage::class, $this->unit)->e($this->trade);
		} elseif ($this->status === SalesModel::UNSATISFIABLE && !$this->context->getTurnOptions()->IsSimulation()) {
			$merchant = $this->trade->Unit();
			$this->message(AcceptUnsatisfiableTradeMessage::class, $this->unit)->e($this->trade)->e($merchant, AcceptUnsatisfiableTradeMessage::MERCHANT);
			$this->message(AcceptTradeUnableMessage::class, $merchant)->e($this->trade)->e($this->unit, AcceptTradeUnableMessage::CUSTOMER);
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

		$isOffer = $this->trade->Trade() === Trade::OFFER;
		$price   = $isOffer ? $this->trade->Price() : $this->trade->Goods();
		$payment = $this->collectPayment($price->Commodity(), $price->Amount());
		if ($payment) {
			$goods    = $isOffer ? $this->trade->Goods() : $this->trade->Price();
			$quantity = new Quantity($goods->Commodity(), $goods->Amount());
			$this->exchange($quantity, $payment);
			$this->tradeMessages($quantity, $payment);
			$this->addTradeEffect();
		}
	}

	private function acceptPieces(): void {
		if ($this->amount === null || $this->amount <= 0 || $this->price !== null) {
			throw new InvalidCommandException($this);
		}

		$goods = $this->range ? $this->checkRange() : $this->checkPieces();
		if ($goods) {
			$isOffer = $this->trade->Trade() === Trade::OFFER;
			$price   = $this->trade->Price();
			if ($isOffer) {
				$payment = $this->collectPayment($price->Commodity(), $this->amount * $price->Amount());
			} else {
				$payment = $this->collectPayment($goods->Commodity(), $this->amount * $goods->Amount());
			}
			if ($payment) {
				if ($isOffer) {
					$quantity = new Quantity($goods->Commodity(), $this->amount);
				} else {
					$quantity = new Quantity($price->Commodity(), $this->amount * $price->Amount());
				}
				$this->exchange($quantity, $payment);
				$this->tradeMessages($quantity, $payment);
				$this->addTradeEffect();
			}
		}
	}

	private function acceptBargain(): void {
		if ($this->amount !== null || $this->price === null) {
			throw new InvalidCommandException($this);
		}

		$isOffer = $this->trade->Trade() === Trade::OFFER;
		$price   = $this->trade->Price();
		if (!$this->context->getTurnOptions()->IsSimulation() && $this->price < $price->Minimum()) {
			if ($isOffer) {
				$this->message(AcceptOfferPriceMessage::class, $this->unit)->e($this->trade)->e($this->trade->Unit(), AcceptOfferAmountMessage::UNIT);
			} else {
				$this->message(AcceptDemandPriceMessage::class, $this->unit)->e($this->trade)->e($this->trade->Unit(), AcceptOfferAmountMessage::UNIT);
			}
			return;
		}

		$goods = $this->trade->Goods();
		if ($isOffer) {
			$payment = $this->collectPayment($price->Commodity(), $this->price);
		} else {
			$payment = $this->collectPayment($goods->Commodity(), $goods->Amount());
		}
		if ($payment) {
			if ($isOffer) {
				$quantity = new Quantity($goods->Commodity(), $goods->Amount());
			} else {
				$quantity = new Quantity($price->Commodity(), $this->price);
			}
			$this->exchange($quantity, $payment);
			$this->tradeMessages($quantity, $payment);
			$this->addTradeEffect();
		}
	}

	private function acceptPiecesBargain(): void {
		if ($this->amount === null || $this->price === null) {
			throw new InvalidCommandException($this);
		}

		$goods = $this->range ? $this->checkRange() : $this->checkPieces();
		if ($goods) {
			$isOffer  = $this->trade->Trade() === Trade::OFFER;
			$proposal = $this->amount * $this->price;
			$price    = $this->trade->Price();
			if (!$this->context->getTurnOptions()->IsSimulation()) {
				if ($isOffer) {
					$minimum = $this->amount * $price->Minimum();
					if ($proposal < $minimum) {
						$this->message(AcceptOfferPriceMessage::class, $this->unit)->e($this->trade)->e($this->trade->Unit(), AcceptOfferAmountMessage::UNIT);
						return;
					}
				} else {
					$maximum = $this->amount * $price->Maximum();
					if ($proposal > $maximum) {
						$this->message(AcceptDemandPriceMessage::class, $this->unit)->e($this->trade)->e($this->trade->Unit(), AcceptOfferAmountMessage::UNIT);
						return;
					}
				}
			}

			if ($isOffer) {
				$payment = $this->collectPayment($price->Commodity(), $proposal);
			} else {
				$payment = $this->collectPayment($goods->Commodity(), $this->amount);
			}
			if ($payment) {
				if ($isOffer) {
					$quantity = new Quantity($goods->Commodity(), $this->amount);
				} else {
					$quantity = new Quantity($price->Commodity(), $proposal);
				}
				$this->exchange($quantity, $payment);
				$this->tradeMessages($quantity, $payment);
				$this->addTradeEffect();
			}
		}
	}

	private function parseTrade(): void {
		if ($this->phrase->count() <= 0) {
			throw new InvalidCommandException($this);
		}
		$this->id = $this->parseId();
		try {
			$trade = Trade::get($this->id);
			foreach ($this->sales as $index => $construction /** @var Construction $construction */) {
				$sales = new Sales($construction);
				if ($sales->has($trade)) {
					$this->index  = $index;
					$this->status = $sales->getStatus($trade);
					$this->trade  = $trade;
				}
			}
		} catch (NotRegisteredException|SalesException) {
		}
	}

	private function parseMerchantTrade(): bool {
		if ($this->phrase->count() <= 0) {
			throw new InvalidCommandException($this);
		}
		$this->id = $this->parseId();
		try {
			$trade = Trade::get($this->id);
			if ($trade->Unit()->Party()->Type() === Type::NPC) {
				$this->trade  = $trade;
				$this->status = $trade->IsSatisfiable() ? SalesModel::AVAILABLE : SalesModel::UNSATISFIABLE;
				return true;
			}
		} catch (NotRegisteredException|SalesException) {
		}
		return false;
	}

	private function parseParameters(): void {
		$goods = $this->trade->Goods();
		$price = $this->trade->Price();
		$n     = $this->phrase->count();

		if ($n > 1) {
			$parameter = strtolower($this->phrase->getParameter(2));

			$this->parseAmountOnly($goods, $price, $parameter);
			if ($n === 2) {
				// HANDEL <trade> *|Alle|Alles|<amount>|<min>-<max>
				return;
			}

			$i         = 3;
			$commodity = $this->parseCommodity($i, $n);
			if ($i < $n) {
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
				if ($i < $n) {
					throw new InvalidCommandException($this);
				}
				if ($payment !== $price->Commodity()) {
					throw new InvalidCommandException($this);
				}
			} else {
				if ($goods->IsVariable()) {
					if ($price->IsVariable()) {
						throw new InvalidCommandException($this);
					}
					if ($commodity === $goods->Commodity()) {
						return;
					}
				}
				if ($price->IsVariable()) {
					if ($commodity === $price->Commodity()) {
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

	private function parseAmountOnly(Deal $goods, Deal $price, string $parameter): void {
		if (in_array($parameter, ['*', 'alle', 'alles'])) {
			$number = '*';
		} elseif (preg_match('/^([0-9]+)-([0-9]+)$/', $parameter, $range) === 1 && $this->parseRange($range)) {
			$number = '-';
		} else {
			$number = $this->parsePositiveAmount($parameter);
		}
		if ($goods->IsVariable()) {
			if ($number === '*') {
				$this->amount = $goods->Maximum();
				$this->range  = [$goods->Minimum(), $this->amount];
			} elseif ($number === '-') {
				$this->amount = $this->range[1];
			} else {
				$this->amount = $number;
			}
			return;
		}
		if ($price->IsVariable()) {
			$this->price = is_int($number) ? $number : $price->Maximum();
			return;
		}
		throw new InvalidCommandException($this);
	}

	private function parsePositiveAmount(string $parameter): int {
		$number = (int)$parameter;
		if ((string)$number !== $parameter || $number <= 0) {
			throw new InvalidCommandException($this);
		}
		return $number;
	}

	private function parseRange(array $range): bool {
		$min = (int)$range[1];
		$max = (int)$range[2];
		if ($min > 0 && $max > 0 && $max !== $min) {
			$this->range = $max > $min ? [$min, $max] : [$max, $min];
			return true;
		}
		return false;
	}

	private function checkPieces(): ?Deal {
		$unit    = $this->trade->Unit();
		$goods   = $this->trade->Goods();
		$maximum = $goods->IsAdapting() ? $this->getAvailableMaximum(): $goods->Maximum();
		if ($this->amount < $goods->Minimum() || $this->amount > $maximum) {
			if ($this->trade->Trade() === Trade::OFFER) {
				$this->message(AcceptOfferAmountMessage::class, $this->unit)->e($this->trade)->e($unit, AcceptOfferAmountMessage::UNIT);
			} else {
				$this->message(AcceptDemandAmountMessage::class, $this->unit)->e($this->trade)->e($unit, AcceptOfferAmountMessage::UNIT);
			}
			return null;
		}

		if ($this->trade->Trade() === Trade::OFFER) {
			$commodity = $goods->Commodity();
			$inventory = $unit->Inventory();
			$reserve   = $inventory[$commodity];
			if ($reserve->Count() < $this->amount) {
				$demand = new Quantity($commodity, $this->amount);
				$this->message(AcceptOfferReserveMessage::class, $this->unit)->e($this->trade)->s($commodity)->e($unit, AcceptOfferAmountMessage::UNIT);
				$this->message(AcceptOfferUnableMessage::class, $unit)->e($this->trade)->i($demand)->e($this->unit, AcceptOfferAmountMessage::UNIT);
				return null;
			}
		}

		return $goods;
	}

	private function checkRange(): ?Deal {
		$unit    = $this->trade->Unit();
		$goods   = $this->trade->Goods();
		$minimum = $goods->Minimum();
		$maximum = $goods->IsAdapting() ? $this->getAvailableMaximum(): $goods->Maximum();
		$range   = [max($this->range[0], $minimum), min($this->range[1], $maximum)];
		if ($range[0] > $range[1]) {
			if ($this->trade->Trade() === Trade::OFFER) {
				$this->message(AcceptOfferAmountMessage::class, $this->unit)->e($this->trade)->e($unit, AcceptOfferAmountMessage::UNIT);
			} else {
				$this->message(AcceptDemandAmountMessage::class, $this->unit)->e($this->trade)->e($unit, AcceptOfferAmountMessage::UNIT);
			}
			return null;
		}

		if ($this->trade->Trade() === Trade::OFFER) {
			$commodity    = $goods->Commodity();
			$inventory    = $unit->Inventory();
			$reserve      = $inventory[$commodity]->Count();
			$this->amount = min($range[1], $reserve);
			if ($this->amount < $range[0]) {
				$this->amount = $range[0];
				$demand       = new Quantity($commodity, $this->amount);
				$this->message(AcceptOfferReserveMessage::class, $this->unit)->e($this->trade)->s($commodity)->e($unit, AcceptOfferAmountMessage::UNIT);
				$this->message(AcceptOfferUnableMessage::class, $unit)->e($this->trade)->i($demand)->e($this->unit, AcceptOfferAmountMessage::UNIT);
				return null;
			}
		}

		return $goods;
	}

	private function collectPayment(Commodity $commodity, int $price): ?Quantity {
		$payment = $this->collectQuantity($this->unit, $commodity, $price);
		if ($payment->Count() < $price) {
			if ($this->trade->Trade() === Trade::OFFER) {
				$this->message(AcceptNoPaymentMessage::class, $this->unit)->s($commodity)->e($this->trade);
			} else {
				$this->message(AcceptNoDeliveryMessage::class, $this->unit)->s($commodity)->e($this->trade);
			}
			return null;
		}
		return $payment;
	}

	private function exchange(Quantity $quantity, Quantity $payment): void {
		/*
		if ($this->trade->Trade() === Trade::DEMAND) {
			$temp     = $quantity;
			$quantity = $payment;
			$payment  = $temp;
		}
		*/
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

		$fee = is_int($this->index) ? $this->market[$this->index]->Fee() : null;
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
			$this->demandMessages($payment, $quantity);
		}
	}

	private function offerMessages(Quantity $quantity, Quantity $payment): void {
		$trade    = $this->trade;
		$merchant = $this->trade->Unit();
		$customer = $this->unit;
		$unit     = AcceptOfferMessage::UNIT;
		$pay      = AcceptOfferMessage::PAYMENT;
		$this->message(AcceptOfferMessage::class, $merchant)->e($trade)->e($customer, $unit)->i($quantity)->i($payment, $pay);
		$this->message(AcceptBoughtMessage::class, $customer)->e($trade)->e($merchant, $unit)->i($quantity)->i($payment, $pay);
	}

	private function demandMessages(Quantity $quantity, Quantity $payment): void {
		$trade    = $this->trade;
		$merchant = $this->trade->Unit();
		$customer = $this->unit;
		$unit     = AcceptOfferMessage::UNIT;
		$pay      = AcceptOfferMessage::PAYMENT;
		$this->message(AcceptDemandMessage::class, $merchant)->e($trade)->e($customer, $unit)->i($quantity)->i($payment, $pay);
		$this->message(AcceptSoldMessage::class, $customer)->e($trade)->e($merchant, $unit)->i($quantity)->i($payment, $pay);
	}

	private function payFee(Unit $unit, Quantity $payment, float $rate): void {
		$fee   = (int)round($rate * $payment->Count());
		$owner = $this->unit->Construction()->Inhabitants()->Owner();
		if ($fee > 0) {
			$quantity = new Quantity($payment->Commodity(), $fee);
			$unit->Inventory()->remove($quantity);
			$owner->Inventory()->add(new Quantity($payment->Commodity(), $fee));
			$this->message(AcceptFeePaidMessage::class, $this->unit)->e($owner)->i($quantity);
			$this->message(AcceptFeeReceivedMessage::class, $owner)->e($this->unit)->i($quantity);
		} else {
			$this->message(AcceptNoFeeMessage::class, $this->unit)->e($this->trade);
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

	private function addTradeEffect(): void {
		$effect   = new TradeEffect(State::getInstance());
		$existing = Lemuria::Score()->find($effect->setUnit($this->unit));
		if ($existing instanceof TradeEffect) {
			$effect = $existing;
		} else {
			Lemuria::Score()->add($effect->addReassignment());
		}
		$effect->Trades()->add($this->trade);
	}
}
