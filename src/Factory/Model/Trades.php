<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Engine\Fantasya\Effect\UnpaidFee;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Market\Sales;
use Lemuria\Model\Fantasya\Market\Trade;
use Lemuria\Model\Fantasya\Unit;

class Trades implements \Countable
{
	protected ?Sales $sales = null;

	protected bool $hasPaid;

	/**
	 * @var array<Trade>
	 */
	protected array $available;

	/**
	 * @var array<Trade>
	 */
	protected array $impossible;

	/**
	 * @var array<Trade>
	 */
	protected array $forbidden;

	public function __construct(Construction $market) {
		if ($market->Extensions()->offsetExists(Market::class)) {
			$this->sales = new Sales($market);
		}
	}

	public function HasMarket(): bool {
		return (bool)$this->sales;
	}

	public function HasPaidFee(): bool {
		return $this->hasPaid;
	}

	public function count(): int {
		return count($this->available) + count($this->impossible) + count($this->forbidden);
	}

	public function forUnit(Unit $unit): Trades {
		$this->checkIfUnitHasPaidFee($unit);
		$this->available  = [];
		$this->impossible = [];
		$this->forbidden  = [];
		foreach ($unit->Trades()->sort() as $trade) {
			$id = $trade->Id()->Id();
			if ($this->sales) {
				if ($this->hasPaid) {
					match ($this->sales->getStatus($trade)) {
						Sales::FORBIDDEN     => $this->forbidden[$id]  = $trade,
						Sales::UNSATISFIABLE => $this->impossible[$id] = $trade,
						default              => $this->available[$id]  = $trade
					};
				} else {
					$this->forbidden[$id] = $trade;
				}
			} else {
				if ($trade->IsSatisfiable()) {
					$this->available[$id] = $trade;
				} else {
					$this->impossible[$id] = $trade;
				}

			}
		}
		return $this;
	}

	/**
	 * @return array<Trade>
	 */
	public function Available(): array {
		return $this->available;
	}

	/**
	 * @return array<Trade>
	 */
	public function Forbidden(): array {
		return $this->forbidden;
	}

	/**
	 * @return array<Trade>
	 */
	public function Impossible(): array {
		return $this->impossible;
	}

	protected function checkIfUnitHasPaidFee(Unit $unit): void {
		$effect        = new UnpaidFee(State::getInstance());
		$this->hasPaid = !Lemuria::Score()->find($effect->setUnit($unit));
	}
}
