<?php
declare (strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Factory\BuilderTrait;

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
}
