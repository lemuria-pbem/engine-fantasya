<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Engine\Fantasya\Effect\UnpaidFee;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Market\Sales as MarketSales;
use Lemuria\Model\Fantasya\Unit;

class Sales extends MarketSales
{
	protected function addTrades(Unit $unit): void {
		if (!$this->hasUnpaidFeeEffect($unit)) {
			parent::addTrades($unit);
		}
	}

	protected function hasUnpaidFeeEffect(Unit $unit): bool {
		$effect = new UnpaidFee(State::getInstance());
		return (bool)Lemuria::Score()->find($effect->setUnit($unit));
	}
}
