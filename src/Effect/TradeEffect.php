<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Extension\Trades;

final class TradeEffect extends AbstractUnitEffect
{
	private Trades $trades;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
		$this->trades = new Trades();
	}

	public function Trades(): Trades {
		return $this->trades;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
