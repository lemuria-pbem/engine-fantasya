<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptForbiddenTradeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptNoMarketMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptNoTradeMessage;
use Lemuria\Engine\Fantasya\Message\Unit\AcceptUnsatisfiableTradeMessage;
use Lemuria\Id;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Exception\SalesException;
use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Market\Sales;
use Lemuria\Model\Fantasya\Market\Trade;

/**
 * Accept a trade from a unit.
 *
 * - HANDEL <trade>
 * - HANDEL <trade> <price> <payment>
 * - HANDEL <trade> <amount> <commodity> <price> <payment>
 */
final class Accept extends UnitCommand
{
	use BuilderTrait;

	protected ?Sales $sales = null;

	protected ?Id $id = null;

	protected ?Trade $trade = null;

	protected ?int $status = null;

	protected ?int $amount = null;

	protected ?int $price = null;

	protected function initialize(): void {
		parent::initialize();
		$construction = $this->unit->Construction();
		if ($construction->Extensions()->offsetExists(Market::class)) {
			$this->sales = new Sales($this->unit->Construction());
			$this->parseTrade();
		}
	}

	protected function run(): void {
		if (!$this->sales) {
			$this->message(AcceptNoMarketMessage::class);
			return;
		}
		if (!$this->trade) {
			$this->message(AcceptNoTradeMessage::class)->p((string)$this->id);
			return;
		}
		if ($this->status === Sales::FORBIDDEN) {
			$this->message(AcceptForbiddenTradeMessage::class)->e($this->trade);
		} elseif ($this->status === Sales::UNSATISFIABLE) {
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

		//TODO trade
	}

	private function acceptPieces(): void {
		if ($this->amount === null || $this->price !== null) {
			throw new InvalidCommandException($this);
		}

		//TODO trade pieces
	}

	private function acceptBargain(): void {
		if ($this->amount !== null || $this->price === null) {
			throw new InvalidCommandException($this);
		}

		//TODO trade bargain
	}

	private function acceptPiecesBargain(): void {
		if ($this->amount === null || $this->price === null) {
			throw new InvalidCommandException($this);
		}

		//TODO trade pieces with bargain
	}

	private function parseTrade(): void {
		if ($this->phrase->count() <= 0) {
			throw new InvalidCommandException($this);
		}
		$this->id = Id::fromId($this->phrase->getParameter());
		try {
			$trade        = Trade::get($this->id);
			$this->status = $this->sales->getStatus($trade);
			$this->trade  = $trade;
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
		$class = $this->phrase->getLine($i, $index - 1);
		return self::createCommodity($class);
	}
}
