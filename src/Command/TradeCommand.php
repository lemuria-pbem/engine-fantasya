<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Command;

use Lemuria\Engine\Fantasya\Context;
use Lemuria\Engine\Fantasya\Exception\UnknownCommandException;
use Lemuria\Engine\Fantasya\Message\Unit\TradeForbiddenCommodityMessage;
use Lemuria\Engine\Fantasya\Message\Unit\TradeForbiddenPaymentMessage;
use Lemuria\Engine\Fantasya\Phrase;
use Lemuria\Lemuria;
use Lemuria\Model\Domain;
use Lemuria\Model\Fantasya\Commodity;
use Lemuria\Model\Fantasya\Commodity\Silver;
use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;
use Lemuria\Model\Fantasya\Market\Deal;
use Lemuria\Model\Fantasya\Market\Trade;

/**
 * Base command to create offers and demands for the market.
 *
 * - ANGEBOT|NACHFRAGE <amount> <commodity> <price> [<commodity>]
 * - ANGEBOT|NACHFRAGE <amount>-<amount> <commodity> <price> [<commodity>]
 * - ANGEBOT|NACHFRAGE * <commodity> <price> [<commodity>]
 * - ANGEBOT|NACHFRAGE <amount> <commodity> <price>-<price> [<commodity>]
 * - ANGEBOT|NACHFRAGE <amount>-<amount> <commodity> <price>-<price> [<commodity>]
 */
abstract class TradeCommand extends UnitCommand
{
	use BuilderTrait;

	protected final const AMOUNT = 0;

	protected final const COMMODITY = 1;

	protected final const PRICE = 2;

	protected final const PAYMENT = 3;

	protected Commodity $silver;

	public function __construct(Phrase $phrase, Context $context) {
		parent::__construct($phrase, $context);
		$this->silver = self::createCommodity(Silver::class);
	}

	protected function createTrade(): Trade {
		$tradeables = $this->getMarket()?->Tradeables();
		$parts      = $this->parseParts();

		$amount    = $parts[self::AMOUNT];
		$commodity = $parts[self::COMMODITY];
		$goods     = is_int($amount) ? new Deal($commodity, $amount) : new Deal($commodity, $amount[0], $amount[1]);
		if ($tradeables && !$tradeables->isAllowed($commodity)) {
			$this->message(TradeForbiddenCommodityMessage::class)->s($commodity);
		}

		$amount  = $parts[self::PRICE];
		$payment = $parts[self::PAYMENT];
		$price   = is_int($amount) ? new Deal($payment, $amount) : new Deal($payment, $amount[0], $amount[1]);
		if ($tradeables && !$tradeables->isAllowed($payment)) {
			$this->message(TradeForbiddenPaymentMessage::class)->s($payment);
		}

		$isRepeat = $this->unit->Party()->Presettings()->IsRepeat();
		$trade    = new Trade();
		$trade->setId(Lemuria::Catalog()->nextId(Domain::Trade));
		return $trade->setGoods($goods)->setPrice($price)->setIsRepeat($isRepeat);
	}

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

	protected function parseParts(): array {
		$factory = $this->context->Factory();
		$parts   = [self::AMOUNT => null, self::COMMODITY => [], self::PRICE => null, self::PAYMENT => null];
		$n       = $this->phrase->count();
		$i       = 1;
		$c       = 0;

		$parameter = $this->phrase->getParameter($i++);
		if ($parameter === '*') {
			$parts[self::AMOUNT] = [1, Deal::ADAPTING_MAX];
		} elseif (preg_match('/^\d+(-\d+)?$/', $parameter, $matches) === 1) {
			$parts[self::AMOUNT] = $this->parseNumber($matches[0]);
		} else {
			throw new UnknownCommandException($this);
		}

		do {
			$parameter = $this->phrase->getParameter($i++);
			if (preg_match('/^\d+(-\d+)?$/', $parameter, $matches) === 1) {
				$parts[self::PRICE] = $this->parseNumber($matches[0]);
				break;
			}
			$parts[self::COMMODITY][] = $parameter;
			$c++;
		} while ($i <= $n);
		if ($c <= 0 || $parts[self::PRICE] === null) {
			throw new UnknownCommandException($this);
		}
		$commodity              = implode(' ', $parts[self::COMMODITY]);
		$parts[self::COMMODITY] = $factory->commodity($commodity);

		$parts[self::PAYMENT] = $i <= $n ? $factory->commodity($this->phrase->getLine($i)) : $this->silver;
		return $parts;
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
}
