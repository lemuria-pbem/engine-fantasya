<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory\Model;

use Lemuria\Engine\Fantasya\Factory\GrammarTrait;
use Lemuria\Exception\LemuriaException;
use Lemuria\Model\Fantasya\Extension\Trades;
use Lemuria\Model\Fantasya\Market\Deals as DealsModel;
use Lemuria\Model\Fantasya\Market\Trade;
use Lemuria\SortMode;

/**
 * Deals are all trades of a merchant that can be delivered.
 */
class Deals extends DealsModel
{
	use GrammarTrait;

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

	protected function sortAlphabetically(): void {
		$this->initDictionary();
		if (!$this->trades->isEmpty()) {
			$this->trades = $this->sortTrades($this->trades);
		}
		if (!$this->unsatisfiable->isEmpty()) {
			$this->unsatisfiable = $this->sortTrades($this->unsatisfiable);
		}
	}

	/**
	 * @param array<Trade> $trades
	 * @return array<Trade>
	 * @noinspection DuplicatedCode
	 */
	private function sortTrades(Trades $trades): Trades {
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

		$trades->clear();
		foreach (array_keys($offers) as $id) {
			$trades->add($ids[$id]);
		}
		foreach (array_keys($demands) as $id) {
			$trades->add($ids[$id]);
		}
		return $trades;
	}
}
