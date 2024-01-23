<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Engine\Fantasya\Message\Unit\TradeForbiddenPaymentMessage;
use Lemuria\Exception\IdException;
use Lemuria\Id;
use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Extension\Valuables;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Market\Deal;
use Lemuria\Model\Fantasya\Unicum;

trait TradeTrait
{
	use BuilderTrait;

	protected function getMarket(): ?Market {
		$extensions = $this->unit->Construction()?->Extensions();
		if ($extensions && $extensions->offsetExists(Market::class)) {
			$market = $extensions[Market::class];
			if ($market instanceof Market) {
				return $market;
			}
		}
		return null;
	}

	protected function parseNumber(string $amount): array|int {
		if (strpos($amount, '-') > 0) {
			$number = explode('-', $amount);
			$min    = (int)$number[0];
			$max    = (int)$number[1];
			return match (true) {
				$min < $max => [$min, $max],
				$min > $max => [$max, $min],
				default => $min
			};
		}
		return (int)$amount;
	}

	protected function createUnicumOffer(): ?Unicum {
		try {
			$id = Id::fromId($this->phrase->getParameter());
		} catch (IdException) {
			return null;
		}
		$treasury = $this->unit->Treasury();
		if (!$treasury->has($id)) {
			return null;
		}
		$price = $this->parsePrice();

		$unicum     = $treasury[$id];
		$extensions = $this->unit->Extensions();
		if ($extensions->offsetExists(Valuables::class)) {
			$valuables = $extensions->offsetGet(Valuables::class);
		} else {
			$valuables = new Valuables($this->unit);
			$extensions->add($valuables);
		}
		$valuables->add($unicum, $price);
		return $unicum;
	}

	protected function parsePrice(): Deal {
		$price      = $this->parseNumber($this->phrase->getParameter(2));
		$payment    = $this->context->Factory()->commodity($this->phrase->getLine(3));
		$tradeables = $this->getMarket()?->Tradeables();
		if ($tradeables && !$tradeables->isAllowed($payment)) {
			$this->message(TradeForbiddenPaymentMessage::class)->s($payment);
		}
		return is_int($price) ? new Deal($payment, $price) : new Deal($payment, $price[0], $price[1]);
	}
}
