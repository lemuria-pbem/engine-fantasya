<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Engine\Fantasya\Effect\UnpaidFee;
use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Exception\LemuriaException;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Construction;
use Lemuria\Model\Fantasya\Extension\Market;
use Lemuria\Model\Fantasya\Market\Sales;
use Lemuria\Model\Fantasya\Market\Trade;
use Lemuria\Model\Fantasya\Unit;
use Lemuria\SortMode;

class Trades implements \Countable
{
	use GrammarTrait;

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

	public function forUnit(Unit $unit): static {
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

	public function sort(SortMode $mode = SortMode::Alphabetically): static {
		switch ($mode) {
			case SortMode::Alphabetically :
				$this->sortAlphabetically();
				break;
			default :
				throw new LemuriaException('Unsupported sort mode: ' . $mode->name);
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

	/**
	 * Sort the set using specified order.
	 */
	protected function sortAlphabetically(): void {
		$this->initDictionary();
		if (!empty($this->available)) {
			$this->available = $this->sortTrades($this->available);
		}
		if (!empty($this->forbidden)) {
			$this->forbidden = $this->sortTrades($this->forbidden);
		}
		if (!empty($this->impossible)) {
			$this->impossible = $this->sortTrades($this->impossible);
		}
	}

	/**
	 * @param array<Trade> $trades
	 * @return array<Trade>
	 * @noinspection DuplicatedCode
	 */
	private function sortTrades(array $trades): array {
		$offers  = [];
		$demands = [];
		$ids     = [];
		foreach ($trades as $trade) {
			$id       = $trade->Id()->Id();
			$ids[$id] = $trade;
			if ($trade->Trade() === Trade::OFFER) {
				$offers[$id] = $this->translateSingleton($trade->Goods()->Commodity());
			} else {
				$demands[$id] = $this->translateSingleton($trade->Goods()->Commodity());
			}
		}
		asort($offers, SORT_LOCALE_STRING);
		asort($demands, SORT_LOCALE_STRING);

		$trades = [];
		foreach (array_keys($offers) as $id) {
			$trades[] = $ids[$id];
		}
		foreach (array_keys($demands) as $id) {
			$trades[] = $ids[$id];
		}
		return $trades;
	}
}
