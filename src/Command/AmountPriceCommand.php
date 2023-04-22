<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Exception\InvalidCommandException;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Factory\TradeTrait;
use Lemuria\Engine\Fantasya\Message\Unit\TradeNotFoundMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TradeNotOursMessage;
use Lemuria\Model\Exception\NotRegisteredException;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Market\Deal;
use Lemuria\Model\Fantasya\Market\Trade;
use Lemuria\Model\Fantasya\Market\Tradeables;

/**
 * Set new amount or price for existing trade.
 */
abstract class AmountPriceCommand extends UnitCommand
{
	use TradeTrait;

	private ?Tradeables $tradeables;

	protected function initialize(): void {
		parent::initialize();
		$this->tradeables = $this->getMarket()?->Tradeables();
	}

	abstract protected function addForbiddenMessage(Commodity $commodity): void;

	protected function parseTrade(): ?Trade {
		$n = $this->phrase->count();
		if ($n < 2) {
			throw new InvalidCommandException($this);
		}

		$id = $this->parseId();
		try {
			$trade = Trade::get($id);
		} catch (NotRegisteredException) {
			$this->message(TradeNotFoundMessage::class)->p((string)$id);
			return null;
		}

		if (!$this->unit->Trades()->offsetExists($id)) {
			$this->message(TradeNotOursMessage::class)->p((string)$id);
			return null;
		}

		return $trade;
	}

	protected function parseDeal(Commodity $commodity): Deal {
		$amount = $this->phrase->getParameter(2);
		if ($amount === '*') {
			$amount = [1, Deal::ADAPTING_MAX];
		} elseif (preg_match('/^\d+(-\d+)?$/', $amount, $matches) === 1) {
			$amount = $this->parseNumber($matches[0]);
		} else {
			throw new UnknownCommandException($this);
		}

		if ($this->phrase->count() > 2) {
			$commodity = $this->context->Factory()->commodity($this->phrase->getLine(3));
		}
		if ($this->tradeables && !$this->tradeables->isAllowed($commodity)) {
			$this->addForbiddenMessage($commodity);
		}

		return is_int($amount) ? new Deal($commodity, $amount) : new Deal($commodity, $amount[0], $amount[1]);
	}
}
