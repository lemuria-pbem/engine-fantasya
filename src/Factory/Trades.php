<?php
declare(strict_types = 1);
namespace Lemuria\Engine\Fantasya\Factory;

class Trades implements \Countable
{
	private int $count = 0;

	private int $maximum = 0;

	public function CanTrade(): bool {
		return $this->count < $this->maximum;
	}

	public function count() {
		return $this->count;
	}

	public function add(int $count = 1): Trades {
		$this->count += $count;
		return $this;
	}

	public function setMaximum(int $maximum): Trades {
		$this->maximum = $maximum;
		return $this;
	}
}
