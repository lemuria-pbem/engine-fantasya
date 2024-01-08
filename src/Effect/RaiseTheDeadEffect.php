<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Effect;

use Lemuria\Engine\Fantasya\Priority;
use Lemuria\Engine\Fantasya\State;
use Lemuria\Lemuria;
use Lemuria\Model\Fantasya\Unit;

final class RaiseTheDeadEffect extends AbstractRegionEffect
{
	protected ?bool $isReassign = null;

	private Unit $summoner;

	private int $raise;

	public function __construct(State $state) {
		parent::__construct($state, Priority::After);
	}

	public function Summoner(): Unit {
		return $this->summoner;
	}

	public function Raise(): int {
		return $this->raise;
	}

	public function setSummoner(Unit $summoner): RaiseTheDeadEffect {
		$this->summoner = $summoner;
		return $this;
	}

	public function setRaise(int $raise): RaiseTheDeadEffect {
		$this->raise = $raise;
		return $this;
	}

	protected function run(): void {
		Lemuria::Score()->remove($this);
	}
}
